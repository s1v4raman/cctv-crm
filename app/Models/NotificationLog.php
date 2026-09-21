<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationLog extends Model
{
    use HasFactory;

    protected $table = 'notification_logs';

    protected $fillable = [
        'channel',
        'event_type',
        'recipient_type',
        'recipient_name',
        'recipient_phone',
        'recipient_email',
        'subject',
        'message_body',
        'action_url',
        'status',
        'reference_type',
        'reference_id',
        'error_message',
        'sent_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function getChannelBadgeClassAttribute(): string
    {
        return match ($this->channel) {
            'whatsapp' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'sms'      => 'bg-amber-100 text-amber-800 border-amber-200',
            'email'    => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            default    => 'bg-slate-100 text-slate-800 border-slate-200',
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'sent'      => 'bg-emerald-600 text-white',
            'delivered' => 'bg-emerald-700 text-white',
            'queued'    => 'bg-sky-600 text-white',
            'failed'    => 'bg-rose-600 text-white',
            default     => 'bg-slate-500 text-white',
        };
    }

    public function getWhatsAppWebUrlAttribute(): ?string
    {
        if ($this->channel !== 'whatsapp' || !$this->recipient_phone) {
            return null;
        }

        $cleanPhone = preg_replace('/[^0-9]/', '', $this->recipient_phone);
        // Prefix with India country code 91 if 10 digits
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone;
        }

        return 'https://api.whatsapp.com/send?phone=' . $cleanPhone . '&text=' . urlencode($this->message_body);
    }
}
