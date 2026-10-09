<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ProjectDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'title',
        'document_type',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'dc_number',
        'dc_date',
        'is_invoiced',
        'invoice_number',
        'invoiced_at',
        'items_summary',
        'uploaded_by',
        'notes',
    ];

    protected $casts = [
        'dc_date' => 'date',
        'is_invoiced' => 'boolean',
        'invoiced_at' => 'datetime',
        'items_summary' => 'array',
        'file_size' => 'integer',
    ];

    public static function documentTypeOptions(): array
    {
        return [
            'delivery_challan'  => 'Delivery Challan (DC)',
            'shop_bill'         => 'Shop / Purchase Bill',
            'quotation'         => 'Quotation / Estimate',
            'purchase_order'    => 'Purchase Order (PO)',
            'invoice'           => 'Tax Invoice',
            'site_photo'        => 'Site Survey / Progress Photo',
            'completion_report' => 'Job Completion Report (JCR)',
            'other'             => 'Other Document',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getDocumentTypeLabelAttribute(): string
    {
        return static::documentTypeOptions()[$this->document_type] ?? ucfirst(str_replace('_', ' ', $this->document_type));
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }

    public function isPdf(): bool
    {
        return strtolower($this->file_type) === 'pdf' || str_ends_with(strtolower($this->file_name), '.pdf');
    }

    public function isImage(): bool
    {
        return in_array(strtolower($this->file_type), ['png', 'jpg', 'jpeg', 'webp', 'gif']);
    }

    public function getUrlAttribute(): string
    {
        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        // Try R2 if configured, else public
        if (config('filesystems.disks.r2.bucket') && Storage::disk('r2')->exists($this->file_path)) {
            return Storage::disk('r2')->url($this->file_path);
        }

        return Storage::disk('public')->url($this->file_path);
    }
}
