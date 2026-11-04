<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Mime\MimeTypes;

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
        'mime_type',
        'storage_disk',
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
            'site_drawing'      => 'Site Blueprint / Wiring Drawing',
            'completion_signoff' => 'Completion Sign-off Sheet',
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

    /**
     * The copy of the file kept in the database, which survives the server disk being wiped.
     */
    public function storedFile(): HasOne
    {
        return $this->hasOne(ProjectDocumentFile::class);
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
        return $this->extension() === 'pdf';
    }

    public function isImage(): bool
    {
        return in_array($this->extension(), ['png', 'jpg', 'jpeg', 'webp', 'gif']);
    }

    public function extension(): string
    {
        return strtolower(pathinfo((string) $this->file_name, PATHINFO_EXTENSION)) ?: strtolower((string) $this->file_type);
    }

    public function contentType(): string
    {
        return $this->mime_type
            ?: (MimeTypes::getDefault()->getMimeTypes($this->extension())[0] ?? 'application/octet-stream');
    }

    /**
     * The file's bytes: from the storage disk while the copy there lasts, otherwise from
     * the database copy. Null when the file was lost before database copies were kept.
     */
    public function fileContents(): ?string
    {
        foreach (array_unique([$this->storage_disk ?: 'public', 'public']) as $disk) {
            if ($this->file_path && config("filesystems.disks.{$disk}") && Storage::disk($disk)->exists($this->file_path)) {
                return Storage::disk($disk)->get($this->file_path);
            }
        }

        $data = $this->storedFile?->data;
        $contents = $data !== null ? base64_decode($data, true) : false;

        return $contents === false ? null : $contents;
    }

    public function getUrlAttribute(): string
    {
        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        // Try R2 if configured, else public
        if (config('filesystems.disks.r2.bucket') && Storage::disk('r2')->exists($this->file_path)) {
            /** @var \Illuminate\Filesystem\FilesystemAdapter $r2Disk */
            $r2Disk = Storage::disk('r2');
            return $r2Disk->url($this->file_path);
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $publicDisk */
        $publicDisk = Storage::disk('public');
        return $publicDisk->url($this->file_path);
    }
}
