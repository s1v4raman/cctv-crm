<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'quotation_id',
        'item_name', 
        'description',
        'quantity',
        'unit', 
        'unit_price', 
        'total',
        'line_total',
        'hsn_code',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Resolve the clean GST HSN / SAC code for quotation and tax invoice display.
     */
    public function getHsnCodeAttribute(): ?string
    {
        if (array_key_exists('hsn_code', $this->attributes) && $this->attributes['hsn_code'] !== null) {
            return trim($this->attributes['hsn_code']);
        }

        return $this->resolveHsnCode(false);
    }

    /**
     * Resolve the full 6 or 8-digit GST HSN / SAC code.
     */
    public function getHsnCodeFullAttribute(): string
    {
        return $this->resolveHsnCode(true);
    }

    /**
     * Intelligent HSN / SAC code resolution across product catalog, categories and item specifications.
     */
    public function resolveHsnCode(bool $fullCode = false): string
    {
        // 1. Direct item attribute if present on model
        if (!empty($this->attributes['hsn_code'])) {
            return trim($this->attributes['hsn_code']);
        }

        // 2. Check linked product catalog record
        if ($this->product) {
            $prodHsn = trim($this->product->hsn_code ?? '');
            $prodText = ($this->product->name ?? '') . ' ' . ($this->product->category ?? '') . ' ' . ($this->product->description ?? '');
            
            // If product has a specific non-camera HSN code, trust it
            if ($prodHsn !== '' && $prodHsn !== '8525') {
                return $fullCode ? $prodHsn : substr($prodHsn, 0, 4);
            }

            // If product has default 8525 but its name/category is NOT a camera (e.g. UPS, NVR, Cable, etc.)
            $inferred = static::inferHsnFromName($prodText);
            if ($inferred !== null) {
                return $fullCode ? $inferred['full'] : $inferred['short'];
            }

            if ($prodHsn !== '') {
                return $fullCode ? $prodHsn : substr($prodHsn, 0, 4);
            }
        }

        // 3. Infer from item_name, description and category
        $itemText = ($this->item_name ?? '') . ' ' . ($this->description ?? '') . ' ' . ($this->category ?? '');
        $inferred = static::inferHsnFromName($itemText);
        if ($inferred !== null) {
            return $fullCode ? $inferred['full'] : $inferred['short'];
        }

        // Standard Default for CCTV hardware
        return $fullCode ? '85258900' : '8525';
    }

    /**
     * Standard Indian GST HSN / SAC classification rules for CCTV, IT, Security and Services.
     */
    public static function inferHsnFromName(string $text): ?array
    {
        $t = strtolower($text);

        // 1. Explicit Services / Installation Charges / AMC / Labour (SAC 9987 / 9983)
        if (preg_match('/\b(?:installation\s*charges?|service\s*charges?|labour\s*charges?|labor\s*charges?|wiring\s*charges?|amc\s*charges?|maintenance\s*charges?|annual\s*maintenance|testing\s*(?:&|and)\s*commissioning)\b/i', $t)
            || (preg_match('/\b(?:service|charges?|labour|labor)\b/i', $t) && preg_match('/\b(?:installation|cabling|mounting|commissioning|fitting|alignment)\b/i', $t))) {
            return ['short' => '9987', 'full' => '998719'];
        }
        if (preg_match('/\b(?:site\s*survey|consulting|cctv\s*survey|area\s*audit|route\s*mapping|proposal\s*design)\b/i', $t)) {
            return ['short' => '9983', 'full' => '998319'];
        }

        // 2. Power Supplies / UPS / SMPS / Adapters / Inverters (HSN 8504)
        if (preg_match('/\b(?:ups|inverter|smps|power\s*supply|adapter|adaptor|transformer|12v\s*power|dc\s*power|power\s*unit|powersupply)\b/i', $t)) {
            return ['short' => '8504', 'full' => '85044090'];
        }

        // 3. Batteries (HSN 8507)
        if (preg_match('/\b(?:battery|lead\s*acid|7ah|12ah|backup\s*battery)\b/i', $t)) {
            return ['short' => '8507', 'full' => '85072000'];
        }

        // 4. Recorders: NVR / DVR / XVR / Digital Video Recorders (HSN 8521)
        if (preg_match('/\b(?:nvr|dvr|xvr|digital\s*video\s*recorder|network\s*video\s*recorder|video\s*recorder|channel\s*nvr|channel\s*dvr)\b/i', $t)) {
            return ['short' => '8521', 'full' => '85219090'];
        }

        // 5. Hard Disks / Surveillance Storage / HDD / SSD (HSN 8471)
        if (preg_match('/\b(?:hard\s*disk|hard\s*drive|hdd|ssd|surveillance\s*hdd|surveillance\s*disk|storage\s*drive|sata|1tb|2tb|4tb|6tb|8tb|10tb|12tb|14tb|16tb)\b/i', $t) && !preg_match('/\b(?:nvr|dvr)\b/i', $t)) {
            return ['short' => '8471', 'full' => '84717020'];
        }

        // 6. Cables & Wires: CAT6, CAT5, Coaxial, 3+1, Optical Fiber, Patch Cords (HSN 8544)
        if (preg_match('/\b(?:cable|wire|cat\s*6|cat6|cat\s*5|coaxial|rg59|rg6|3\+1|optical\s*fiber|patch\s*cord|patch\s*cords|copper\s*cable|utp)\b/i', $t)) {
            return ['short' => '8544', 'full' => '85444999'];
        }

        // 7. Network Switches / Routers / Access Points / Media Converters (HSN 8517)
        if (preg_match('/\b(?:switch|poe\s*switch|gigabit\s*switch|router|access\s*point|ap|wi-?fi\s*router|media\s*converter|sfp|transceiver|network\s*switch)\b/i', $t)) {
            return ['short' => '8517', 'full' => '85176290'];
        }

        // 8. Racks & Server Enclosures: 4U, 6U, 9U, 12U, 15U, 24U, 42U (HSN 9403)
        if (preg_match('/\b(?:rack|server\s*rack|network\s*rack|wall\s*mount\s*rack|4u|6u|9u|12u|15u|24u|cabinet)\b/i', $t)) {
            return ['short' => '9403', 'full' => '94032090'];
        }

        // 9. Connectors, Patch Panels, Faceplates, Keystone I/O (HSN 8536)
        if (preg_match('/\b(?:rj45|bnc|dc\s*pin|connector|keystone|patch\s*panel|faceplate|i\/o|terminal\s*block)\b/i', $t)) {
            return ['short' => '8536', 'full' => '85366990'];
        }

        // 10. Junction Boxes / Conduit Pipes / Casings (HSN 8538 / 3917)
        if (preg_match('/\b(?:junction\s*box|back\s*box|camera\s*box|conduit|pvc\s*pipe|casing|enclosure)\b/i', $t)) {
            return ['short' => '8538', 'full' => '85381010'];
        }

        // 11. Electromagnetic Locks / Strike Locks / Drop Bolts (HSN 8301)
        if (preg_match('/\b(?:em\s*lock|electromagnetic\s*lock|strike\s*lock|drop\s*bolt|magnetic\s*lock|door\s*lock)\b/i', $t)) {
            return ['short' => '8301', 'full' => '83014090'];
        }

        // 12. Biometric / RFID Cards / Access Terminals (HSN 8543 / 8523)
        if (preg_match('/\b(?:smart\s*card|rfid\s*card|proximity\s*card|mifare|tag)\b/i', $t)) {
            return ['short' => '8523', 'full' => '85235200'];
        }
        if (preg_match('/\b(?:biometric|fingerprint|face\s*recognition|access\s*control|attendance\s*terminal|exit\s*button|push\s*button)\b/i', $t)) {
            return ['short' => '8543', 'full' => '85437099'];
        }

        // 13. Display / Monitors / Screen (HSN 8528)
        if (preg_match('/\b(?:monitor|display|led\s*tv|screen|cctv\s*monitor|lcd)\b/i', $t)) {
            return ['short' => '8528', 'full' => '85285900'];
        }

        // 14. CCTV Cameras: Bullet, Dome, PTZ, Wi-Fi, ColorVu, Eyeball (HSN 8525)
        if (preg_match('/\b(?:camera|bullet|dome|ptz|ip\s*camera|cctv|colorvu|night\s*vision|eyeball|pan\s*tilt)\b/i', $t)) {
            return ['short' => '8525', 'full' => '85258900'];
        }

        // 15. General Services fallback (SAC 9987)
        if (preg_match('/\b(?:service|installation|configuration|commissioning|maintenance|amc|repair|labour|labor|fitting|wiring charges|testing|civil work)\b/i', $t)) {
            return ['short' => '9987', 'full' => '998719'];
        }

        return null;
    }


}