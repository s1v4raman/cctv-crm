<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseOrderItem;

/**
 * Per-product stock picture for one project:
 *
 *   given to site = given from the storeroom − returned + used straight from stock
 *   balance on site = given to site − used − wastage
 *
 * plus what is left in the storeroom and still on order from suppliers.
 */
class ProjectMaterialLedger
{
    /** Units that mean an item is counted in metres, i.e. cable */
    public const METRE_UNITS = ['m', 'mtr', 'mtrs', 'meter', 'meters', 'metre', 'metres'];

    /** Purchase orders whose missing quantity is still expected from the supplier */
    public const OPEN_PO_STATUSES = ['ordered', 'partially_received'];

    public static function isMetreUnit(?string $unit): bool
    {
        return in_array(strtolower(trim((string) $unit)), self::METRE_UNITS, true);
    }

    public static function isCableProduct(?Product $product): bool
    {
        return $product !== null
            && (strtolower(trim((string) $product->category)) === 'cable' || self::isMetreUnit($product->unit));
    }

    /**
     * @return array{rows: list<array<string, mixed>>, history: list<array<string, mixed>>, categories: list<string>, cable: array<string, mixed>, hardware: array<string, float>}
     */
    public static function forProject(Project $project): array
    {
        $project->loadMissing([
            'materials.product',
            'materialConsumptions.product',
            'materialConsumptions.projectMaterial.product',
            'materialConsumptions.task',
            'materialConsumptions.consumedBy',
            'materialIssues.product',
            'materialIssues.worker',
            'materialIssues.user',
            'materialIssues.creator',
        ]);

        $rows = [];
        $history = [];

        foreach ($project->materials as $material) {
            $key = $material->product_id ? 'p' . $material->product_id : 'm' . $material->id;
            self::ensureRow($rows, $key, $material->product, $material->item_name, $material->unit);
            if (self::sameUnit($material->unit, $rows[$key]['unit'])) {
                $rows[$key]['planned'] += (float) $material->quantity;
            } else {
                // e.g. planned as "1 roll" while the storeroom counts metres: shown as planned, not compared
                $rows[$key]['planned_other'][] = self::fmt((float) $material->quantity) . ' ' . $material->unit;
            }
        }

        foreach ($project->materialIssues as $issue) {
            $key = 'p' . $issue->product_id;
            self::ensureRow($rows, $key, $issue->product, $issue->product?->name ?? 'Product', $issue->unit);
            $rows[$key][$issue->isReturn() ? 'returned' : 'issued'] += (float) $issue->quantity;

            $history[] = [
                'id' => $issue->id,
                'key' => $key,
                'kind' => $issue->isReturn() ? 'return' : 'issue',
                'date' => $issue->issued_on,
                // On the same day: given first, then used, then returned
                'sort' => $issue->issued_on?->format('Y-m-d') . sprintf('-%d-%010d', $issue->isReturn() ? 2 : 0, $issue->id),
                'quantity' => (float) $issue->quantity,
                'wastage' => 0.0,
                'unit' => $issue->unit ?: $issue->product?->unit,
                'who' => $issue->handled_by_name,
                'detail' => $issue->notes,
                'drum' => $issue->drum_or_batch_no,
                'by' => $issue->creator?->name,
            ];
        }

        foreach ($project->materialConsumptions as $use) {
            $product = $use->product ?? $use->projectMaterial?->product;
            if ($product) {
                $key = 'p' . $product->id;
            } elseif ($use->project_material_id) {
                $key = 'm' . $use->project_material_id;
            } elseif (self::isMetreUnit($use->unit)) {
                $key = 'cable-other';
            } else {
                $key = 'x' . md5(strtolower(trim($use->item_name)));
            }

            $name = $key === 'cable-other' ? 'Cable not linked to a product' : ($use->projectMaterial?->item_name ?? $use->item_name);
            self::ensureRow($rows, $key, $product, $name, $use->unit, $key === 'cable-other');

            $used = (float) $use->quantity_used;
            $wastage = (float) $use->wastage_quantity;
            $rows[$key]['used'] += $used;
            $rows[$key]['wastage'] += $wastage;
            // Used straight from the storeroom, without being given to the site first
            if ($use->deduct_from_stock && $use->stock_movement_id) {
                $rows[$key]['direct'] += $used + $wastage;
            }

            $date = $use->consumed_at ?? $use->created_at;
            $history[] = [
                'id' => $use->id,
                'key' => $key,
                'kind' => 'use',
                'date' => $date,
                'sort' => $date?->format('Y-m-d') . sprintf('-1-%010d', $use->id),
                'quantity' => $used,
                'wastage' => $wastage,
                'unit' => $use->unit,
                'who' => $use->consumedBy?->name,
                'detail' => trim(($use->task?->title ?? '') . ($use->notes ? ' · ' . $use->notes : ''), ' ·'),
                'drum' => $use->drum_or_batch_no,
                'by' => $use->consumedBy?->name,
            ];
        }

        $onOrder = self::onOrderByProduct(array_filter(array_column($rows, 'product_id')));

        foreach ($rows as &$row) {
            $row['on_order'] = $row['product_id'] ? (float) ($onOrder[$row['product_id']] ?? 0) : null;
            $row['given'] = $row['issued'] - $row['returned'] + $row['direct'];
            $row['balance'] = $row['given'] - $row['used'] - $row['wastage'];
            // Material used without being given (e.g. bought locally) still counts as supplied
            $supplied = max($row['given'], $row['used'] + $row['wastage']);
            $row['still_to_give'] = max(0.0, $row['planned'] - $supplied);

            $row['warning'] = null;
            if ($row['given'] > 0 && $row['balance'] < 0) {
                $row['warning'] = 'Used ' . self::fmt(-$row['balance']) . ' more than was given';
            } elseif ($row['in_store'] !== null && $row['still_to_give'] > $row['in_store'] + $row['on_order']) {
                $row['warning'] = 'Storeroom short by ' . self::fmt($row['still_to_give'] - $row['in_store'] - $row['on_order']);
            }
        }
        unset($row);

        $rows = array_values($rows);
        usort($rows, fn ($a, $b) => [$b['is_cable'], $a['name']] <=> [$a['is_cable'], $b['name']]);
        usort($history, fn ($a, $b) => strcmp($b['sort'], $a['sort']));

        $categories = array_values(array_unique(array_column($rows, 'category')));
        sort($categories);
        if (in_array('Cable', $categories, true)) {
            $categories = array_values(array_merge(['Cable'], array_diff($categories, ['Cable'])));
        }

        return [
            'rows' => $rows,
            'history' => $history,
            'categories' => $categories,
            'cable' => self::cableSummary($rows),
            'hardware' => self::hardwareSummary($rows),
        ];
    }

    /**
     * What is on site now for one product: given to site − used − wastage.
     */
    public static function siteBalance(Project $project, int $productId): float
    {
        $project->unsetRelation('materialIssues')->unsetRelation('materialConsumptions');

        foreach (self::forProject($project)['rows'] as $row) {
            if ($row['product_id'] === $productId) {
                return $row['balance'];
            }
        }

        return 0.0;
    }

    private static function ensureRow(array &$rows, string $key, ?Product $product, string $name, ?string $unit, bool $forceCable = false): void
    {
        if (isset($rows[$key])) {
            return;
        }

        $isCable = $forceCable || ($product ? self::isCableProduct($product) : self::isMetreUnit($unit));

        $rows[$key] = [
            'key' => $key,
            'product_id' => $product?->id,
            'name' => $product?->name ?? $name,
            'sku' => $product?->sku,
            'unit' => $product?->unit ?: ($isCable ? 'm' : ($unit ?: 'nos')),
            'is_cable' => $isCable,
            'category' => $isCable ? 'Cable' : ($product?->category ? ucfirst(strtolower(trim($product->category))) : 'Other'),
            'in_store' => $product ? (float) $product->stock_quantity : null,
            'planned' => 0.0,
            'planned_other' => [],
            'issued' => 0.0,
            'returned' => 0.0,
            'direct' => 0.0,
            'used' => 0.0,
            'wastage' => 0.0,
        ];
    }

    /**
     * @param  array<int, int>  $productIds
     * @return array<int, int> pending quantity on open purchase orders, by product id
     */
    private static function onOrderByProduct(array $productIds): array
    {
        if ($productIds === []) {
            return [];
        }

        return PurchaseOrderItem::whereIn('product_id', $productIds)
            ->whereHas('purchaseOrder', fn ($q) => $q->whereIn('status', self::OPEN_PO_STATUSES))
            ->get()
            ->groupBy('product_id')
            ->map(fn ($items) => $items->sum(fn ($item) => $item->pendingQuantity()))
            ->all();
    }

    private static function cableSummary(array $rows): array
    {
        $cable = array_filter($rows, fn ($r) => $r['is_cable']);
        $given = array_sum(array_column($cable, 'given'));
        $used = array_sum(array_column($cable, 'used'));
        $wastage = array_sum(array_column($cable, 'wastage'));

        $stocked = array_filter($cable, fn ($r) => $r['product_id'] !== null);
        $allCable = $stocked === [];
        if ($allCable) {
            // No cable product on this project yet: show every cable product in the storeroom
            $products = Product::where('is_active', true)->get()->filter(fn ($p) => self::isCableProduct($p));
            $inStore = (float) $products->sum('stock_quantity');
            $onOrder = (float) array_sum(self::onOrderByProduct($products->pluck('id')->all()));
        } else {
            $inStore = array_sum(array_column($stocked, 'in_store'));
            $onOrder = array_sum(array_column($stocked, 'on_order'));
        }

        return [
            'given' => $given,
            'pulled' => $used,
            'wastage' => $wastage,
            'balance' => $given > 0 ? $given - $used - $wastage : null,
            'in_store' => $inStore,
            'on_order' => $onOrder,
            'all_cable' => $allCable,
        ];
    }

    private static function hardwareSummary(array $rows): array
    {
        $hardware = array_filter($rows, fn ($r) => !$r['is_cable']);

        return [
            'given' => array_sum(array_column($hardware, 'given')),
            'installed' => array_sum(array_column($hardware, 'used')),
        ];
    }

    private static function sameUnit(?string $a, ?string $b): bool
    {
        $a = strtolower(trim((string) $a));
        $b = strtolower(trim((string) $b));
        $counted = ['nos', 'no', 'pcs', 'pc', 'unit', 'units', 'each'];

        return $a === $b
            || (self::isMetreUnit($a) && self::isMetreUnit($b))
            || (in_array($a, $counted, true) && in_array($b, $counted, true));
    }

    private static function fmt(float $n): string
    {
        return rtrim(rtrim(number_format($n, 2), '0'), '.');
    }
}
