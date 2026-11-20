<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    public const TYPE_RECEIPT = 'receipt';
    public const TYPE_ISSUE = 'issue';
    public const TYPE_RETURN_TO_VENDOR = 'return_to_vendor';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_OPENING = 'opening';

    protected $fillable = [
        'product_id',
        'movement_type', // receipt, issue, return_to_vendor, adjustment, opening
        'type',          // legacy: in, out, adjustment, job_installation, return
        'qty',           // signed: positive for additions, negative for deductions
        'quantity',      // legacy absolute quantity
        'balance_after',
        'unit_cost',
        'reference_type',
        'reference_id',
        'project_id',
        'note',
        'notes',
        'created_by',
        'user_id',
    ];

    protected static function booted(): void
    {
        static::saving(function (StockMovement $m) {
            // Sync movement_type and type
            if (empty($m->movement_type) && !empty($m->type)) {
                $m->movement_type = match ($m->type) {
                    'in' => self::TYPE_RECEIPT,
                    'out', 'job_installation' => self::TYPE_ISSUE,
                    'return' => self::TYPE_RETURN_TO_VENDOR,
                    'adjustment' => self::TYPE_ADJUSTMENT,
                    default => self::TYPE_RECEIPT,
                };
            } elseif (empty($m->type) && !empty($m->movement_type)) {
                $m->type = match ($m->movement_type) {
                    self::TYPE_RECEIPT, self::TYPE_OPENING => 'in',
                    self::TYPE_ISSUE => 'out',
                    self::TYPE_RETURN_TO_VENDOR => 'return',
                    self::TYPE_ADJUSTMENT => 'adjustment',
                    default => 'in',
                };
            }

            // Sync qty and quantity
            if ($m->qty !== null && $m->quantity === null) {
                $m->quantity = abs($m->qty);
            } elseif ($m->quantity !== null && $m->qty === null) {
                $isNegative = in_array($m->type, ['out', 'job_installation', 'return']) 
                    || in_array($m->movement_type, [self::TYPE_ISSUE, self::TYPE_RETURN_TO_VENDOR]);
                $m->qty = $isNegative ? -abs($m->quantity) : abs($m->quantity);
            }

            // Sync notes and note
            if (empty($m->note) && !empty($m->notes)) {
                $m->note = $m->notes;
            } elseif (empty($m->notes) && !empty($m->note)) {
                $m->notes = $m->note;
            }

            // Sync created_by and user_id
            if (empty($m->created_by) && !empty($m->user_id)) {
                $m->created_by = $m->user_id;
            } elseif (empty($m->user_id) && !empty($m->created_by)) {
                $m->user_id = $m->created_by;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'qty'           => 'integer',
            'quantity'      => 'integer',
            'balance_after' => 'integer',
            'unit_cost'     => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
