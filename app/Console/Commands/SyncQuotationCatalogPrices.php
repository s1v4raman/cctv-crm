<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Console\Command;

class SyncQuotationCatalogPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quotations:sync-catalog-prices {--quotation_id= : Specific quotation ID to sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync admin-denoted product prices from catalog to zero-price quotation items';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $quotationId = $this->option('quotation_id');

        $query = QuotationItem::query();
        if ($quotationId) {
            $query->where('quotation_id', $quotationId);
        } else {
            $query->where('unit_price', '<=', 0);
        }

        $items = $query->get();

        if ($items->isEmpty()) {
            $this->info('No zero-price quotation items found to sync.');
            return 0;
        }

        $syncedCount = 0;
        $affectedQuotations = [];

        foreach ($items as $item) {
            $catalogProduct = null;
            if ($item->product_id) {
                $catalogProduct = Product::find($item->product_id);
            }
            if (!$catalogProduct && $item->item_name) {
                $catalogProduct = Product::where('name', trim($item->item_name))
                    ->orWhere('sku', trim($item->item_name))
                    ->first();
            }

            if ($catalogProduct && (float) $catalogProduct->unit_price > 0) {
                $price = (float) $catalogProduct->unit_price;
                $lineTotal = (float) $item->quantity * $price;

                $item->update([
                    'product_id' => $catalogProduct->id,
                    'unit_price' => $price,
                    'total' => $lineTotal,
                ]);

                $syncedCount++;
                $affectedQuotations[$item->quotation_id] = true;
                $this->line("Synced item #{$item->id} '{$item->item_name}' to ₹{$price} (Quotation #{$item->quotation_id})");
            }
        }

        foreach (array_keys($affectedQuotations) as $qid) {
            $quotation = Quotation::find($qid);
            if ($quotation) {
                $quotation->recalculateTotals();
                $this->info("Recalculated totals for Quotation #{$quotation->quotation_no} (New Total: ₹{$quotation->total})");
            }
        }

        $this->info("Successfully synced {$syncedCount} item(s) across " . count($affectedQuotations) . " quotation(s).");
        return 0;
    }
}
