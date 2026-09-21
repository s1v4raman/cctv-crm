<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RmaClaim extends Model
{
    use HasFactory;

    protected $table = 'rma_claims';

    protected $fillable = [
        'rma_no',
        'supplier_id',
        'product_id',
        'installed_equipment_id',
        'service_ticket_id',
        'lead_id',
        'faulty_serial_number',
        'faulty_mac_address',
        'issue_description',
        'fault_category',
        'warranty_status_at_claim',
        'status',
        'vendor_rma_ref',
        'shipping_courier',
        'tracking_number',
        'dispatched_date',
        'expected_return_date',
        'received_from_vendor_date',
        'resolution_type',
        'replacement_serial_number',
        'replacement_mac_address',
        'replacement_warranty_expiry',
        'vendor_repair_notes',
        'service_cost',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'dispatched_date'             => 'date',
            'expected_return_date'        => 'date',
            'received_from_vendor_date'   => 'date',
            'replacement_warranty_expiry' => 'date',
            'service_cost'                => 'decimal:2',
        ];
    }

    public static function generateRmaNo(): string
    {
        $year = date('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return sprintf('RMA-%s-%04d', $year, $count);
    }

    public static function faultCategories(): array
    {
        return [
            'no_power'        => '⚡ No Power / Power Supply Failure',
            'sensor_defect'   => '📷 Sensor Fault / Purple Image / Artifacts',
            'ir_led_failure'  => '🌙 Night Vision IR LEDs Inoperative',
            'firmware_crash'  => '💾 Firmware Brick / Boot Loop',
            'port_damage'     => '🔌 RJ45 / BNC Port / Surge Damage',
            'lens_blur'       => '🔍 Lens Blur / Autofocus Motor Stuck',
            'hdd_bad_sectors' => '💿 Hard Drive Bad Sectors / Read Error',
            'other'           => '⚠️ Other Hardware Defect',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'draft'             => 'Draft / Created',
            'shipped_to_vendor' => 'Shipped to Vendor',
            'in_vendor_repair'  => 'In Vendor Repair',
            'replaced'          => 'Unit Replaced',
            'repaired'          => 'Unit Repaired',
            'credit_note'       => 'Credit Note Issued',
            'rejected'          => 'Claim Rejected',
            'closed'            => 'Closed / Re-deployed',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function installedEquipment(): BelongsTo
    {
        return $this->belongsTo(InstalledEquipment::class);
    }

    public function serviceTicket(): BelongsTo
    {
        return $this->belongsTo(ServiceTicket::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(RmaStatusLog::class)->latest();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusLabels()[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getFaultCategoryLabelAttribute(): string
    {
        return self::faultCategories()[$this->fault_category] ?? ucfirst(str_replace('_', ' ', $this->fault_category));
    }

    public function getTurnaroundDaysAttribute(): ?int
    {
        if ($this->dispatched_date) {
            $endDate = $this->received_from_vendor_date ?: Carbon::now();
            return (int) $this->dispatched_date->diffInDays($endDate);
        }
        return null;
    }

    public function getBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'draft'             => 'bg-slate-100 text-slate-700 border-slate-200',
            'shipped_to_vendor' => 'bg-sky-100 text-sky-800 border-sky-200',
            'in_vendor_repair'  => 'bg-amber-100 text-amber-800 border-amber-200',
            'replaced'          => 'bg-purple-100 text-purple-800 border-purple-200',
            'repaired'          => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'credit_note'       => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'rejected'          => 'bg-rose-100 text-rose-800 border-rose-200',
            'closed'            => 'bg-emerald-600 text-white border-transparent',
            default             => 'bg-slate-100 text-slate-700 border-slate-200',
        };
    }
}
