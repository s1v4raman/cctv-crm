<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Quotation extends Model
{
    protected $fillable = [
        'lead_id',
        'quotation_no',
        'quotation_date',
        'valid_until',
        'subtotal',
        'discount',
        'tax_percent',
        'tax_amount',
        'total',
        'status',
        'sent_at',
        'accepted_at',
        'rejected_at',
        'rejection_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quotation_date' => 'date',
            'valid_until' => 'date',
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function installationJob()
    {
        return $this->hasOne(InstallationJob::class);
    }

    public function job()
    {
        return $this->installationJob();
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(QuotationStatusHistory::class);
    }

    public function getGrandTotalAttribute(): float
    {
        return (float) ($this->total ?? 0);
    }

    public function recalculateTotals(): void
    {
        $subtotal = (float) $this->items()->sum('total');

        $discount = min((float) $this->discount, $subtotal);
        $taxableAmount = $subtotal - $discount;

        $taxAmount = $taxableAmount * ((float) $this->tax_percent / 100);
        $grandTotal = $taxableAmount + $taxAmount;

        $this->update([
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax_amount' => $taxAmount,
            'total' => $grandTotal,
        ]);
    }

    public function changeStatus(string $newStatus, ?string $remarks = null): void
    {
        $oldStatus = $this->status;

        $updates = [
            'status' => $newStatus,
        ];

        if ($newStatus === 'sent') {
            $updates['sent_at'] = now();
        }

        if ($newStatus === 'accepted') {
            $updates['accepted_at'] = now();
        }

        if ($newStatus === 'rejected') {
            $updates['rejected_at'] = now();
            $updates['rejection_reason'] = $remarks;
        }

        $this->update($updates);

        $this->statusHistories()->create([
            'user_id' => Auth::id(),
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'remarks' => $remarks,
        ]);
    }

    public static function numberToWords($num)
    {
        $ones = array(
            0 => "", 1 => "one", 2 => "two", 3 => "three", 4 => "four", 5 => "five", 6 => "six", 7 => "seven", 8 => "eight", 9 => "nine",
            10 => "ten", 11 => "eleven", 12 => "twelve", 13 => "thirteen", 14 => "fourteen", 15 => "fifteen", 16 => "sixteen", 17 => "seventeen", 18 => "eighteen", 19 => "nineteen"
        );
        $tens = array(
            0 => "", 1 => "ten", 2 => "twenty", 3 => "thirty", 4 => "forty", 5 => "fifty", 6 => "sixty", 7 => "seventy", 8 => "eighty", 9 => "ninety"
        );
        $hundreds = array(
            "hundred", "thousand", "million", "billion", "trillion"
        );
        $num = number_format($num, 2, ".", ",");
        $num_arr = explode(".", $num);
        $wholenum = $num_arr[0];
        $decnum = $num_arr[1];
        $whole_arr = array_reverse(explode(",", $wholenum));
        krsort($whole_arr);
        $rettxt = "";
        foreach ($whole_arr as $key => $i) {
            if ($i < 20) {
                $rettxt .= $ones[(int)$i];
            } elseif ($i < 100) {
                $rettxt .= $tens[substr($i, 0, 1)];
                if (substr($i, 1, 1) > 0) {
                    $rettxt .= " " . $ones[substr($i, 1, 1)];
                }
            } else {
                $rettxt .= $ones[substr($i, 0, 1)] . " " . $hundreds[0];
                if (substr($i, 1, 2) < 20) {
                    if (substr($i, 1, 2) > 0) {
                        $rettxt .= " " . $ones[(int)substr($i, 1, 2)];
                    }
                } else {
                    if (substr($i, 1, 1) > 0) {
                        $rettxt .= " " . $tens[substr($i, 1, 1)];
                    }
                    if (substr($i, 2, 1) > 0) {
                        $rettxt .= " " . $ones[substr($i, 2, 1)];
                    }
                }
            }
            if ($key > 0) {
                $rettxt .= " " . $hundreds[$key] . " ";
            }
        }
        if ($decnum > 0) {
            $rettxt .= " and ";
            if ($decnum < 20) {
                $rettxt .= $ones[(int)$decnum];
            } else {
                $rettxt .= $tens[substr($decnum, 0, 1)];
                if (substr($decnum, 1, 1) > 0) {
                    $rettxt .= " " . $ones[substr($decnum, 1, 1)];
                }
            }
            $rettxt .= " paise";
        }
        return empty(trim($rettxt)) ? "zero" : trim($rettxt);
    }

    /**
     * Get clean normalized phone number for WhatsApp.
     */
    public function getWhatsAppPhone(?string $customPhone = null): string
    {
        $raw = $customPhone ?? ($this->lead?->phone ?? '');
        $phone = preg_replace('/[^0-9]/', '', (string) $raw);
        if (str_starts_with($phone, '0') && strlen($phone) === 11) {
            return '91' . substr($phone, 1);
        }
        if (strlen($phone) === 10) {
            return '91' . $phone;
        }
        return $phone;
    }

    /**
     * Generate structured WhatsApp message for a given PDF format.
     */
    public function getWhatsAppFormattedMessage(string $format = '1'): string
    {
        $leadName = $this->lead?->customer_name ?? 'Valued Customer';
        $companyName = config('app.name', 'Precision IT Systems');
        $validUntil = $this->valid_until ? $this->valid_until->format('d M Y') : '15 days from issue';
        $quoteDate = ($this->quotation_date ?? $this->created_at ?? now())->format('d M Y');

        $formatName = match($format) {
            '2' => 'Technical Detailed Quotation',
            '3' => 'Classic Formal Quotation',
            default => 'Executive Modern Quotation',
        };

        $itemsSummary = "";
        if ($this->items->isNotEmpty()) {
            foreach ($this->items as $item) {
                $productName = $item->item_name ?: ($item->product ? $item->product->name : ($item->description ?: 'CCTV Equipment'));
                $qty = number_format((float)$item->quantity, 0);
                $total = number_format((float)$item->total, 2);
                $itemsSummary .= "• {$qty}x {$productName} - ₹{$total}\n";
            }
        }

        $pdfUrl = route('quotations.public-pdf', ['quotation' => $this, 'format' => $format]);
        $payUrl = route('payment.checkout.quotation', $this);

        $msg = "📹 CCTV INSTALLATION & SECURITY PROPOSAL\n"
             . "----------------------------------------------\n"
             . "Dear *{$leadName}*,\n\n"
             . "Thank you for contacting {$companyName}. Please find your official *{$formatName}* details below:\n\n"
             . "📋 *Quotation No:* #{$this->quotation_no}\n"
             . "📅 *Date:* {$quoteDate}\n"
             . "⏳ *Valid Until:* {$validUntil}\n\n";

        if (!empty($itemsSummary)) {
            $msg .= "📦 *Equipment & Scope:*\n" . $itemsSummary . "\n";
        }

        $msg .= "💰 *Subtotal:* ₹" . number_format((float)$this->subtotal, 2) . "\n";
        if ((float)$this->discount > 0) {
            $msg .= "🏷️ *Special Discount:* -₹" . number_format((float)$this->discount, 2) . "\n";
        }
        if ((float)$this->tax_amount > 0) {
            $taxPercent = number_format((float)($this->tax_percent ?? 18), 0);
            $msg .= "🧾 *GST ({$taxPercent}%):* ₹" . number_format((float)$this->tax_amount, 2) . "\n";
        }
        $msg .= "💵 *Grand Total:* ₹" . number_format((float)$this->total, 2) . "\n\n"
             . "----------------------------------------------\n"
             . "📄 *Download & View PDF Proposal:*\n{$pdfUrl}\n\n"
             . "💳 *Pay 50% Advance Online (UPI / Card):*\n{$payUrl}\n\n"
             . "Please review the attached proposal PDF. Reply directly to this chat for any questions or customization.\n\n"
             . "Best Regards,\n"
             . "*{$companyName}*";

        return $msg;
    }

    /**
     * Get direct WhatsApp Link (Universal Web & Mobile App Dispatcher).
     */
    public function getWhatsAppUrl(string $format = '1', ?string $customPhone = null, ?string $customMsg = null): string
    {
        $phone = $this->getWhatsAppPhone($customPhone);
        $text = rawurlencode($customMsg ?: $this->getWhatsAppFormattedMessage($format));

        if (empty($phone)) {
            return "https://api.whatsapp.com/send?text={$text}";
        }

        return "https://api.whatsapp.com/send?phone={$phone}&text={$text}";
    }

    /**
     * Get WhatsApp Web Direct Link (web.whatsapp.com).
     */
    public function getWhatsAppWebUrl(string $format = '1', ?string $customPhone = null, ?string $customMsg = null): string
    {
        $phone = $this->getWhatsAppPhone($customPhone);
        $text = rawurlencode($customMsg ?: $this->getWhatsAppFormattedMessage($format));

        if (empty($phone)) {
            return "https://web.whatsapp.com/send?text={$text}";
        }

        return "https://web.whatsapp.com/send?phone={$phone}&text={$text}";
    }

    /**
     * Get direct WhatsApp Desktop scheme (whatsapp://).
     */
    public function getWhatsAppAppUrl(string $format = '1', ?string $customPhone = null, ?string $customMsg = null): string
    {
        $phone = $this->getWhatsAppPhone($customPhone);
        $text = rawurlencode($customMsg ?: $this->getWhatsAppFormattedMessage($format));

        if (empty($phone)) {
            return "whatsapp://send?text={$text}";
        }

        return "whatsapp://send?phone={$phone}&text={$text}";
    }

    /**
     * Get direct SMS Link (sms: scheme for native mobile / desktop messaging).
     */
    public function getSmsUrl(string $format = '1', ?string $customPhone = null, ?string $customMsg = null): string
    {
        $phone = $this->getWhatsAppPhone($customPhone);
        $pdfUrl = route('quotations.public-pdf', ['quotation' => $this, 'format' => $format]);
        $leadName = $this->lead?->customer_name ?? 'Customer';
        $company = config('app.name', 'Precision IT Systems');
        $defaultMsg = "Hello {$leadName}, your CCTV Quotation #{$this->quotation_no} (Grand Total: Rs. " . number_format((float)$this->total, 2) . ") is ready. View PDF: {$pdfUrl} - {$company}";
        $text = rawurlencode($customMsg ?: $defaultMsg);

        $phonePart = $phone ? '+' . (str_starts_with($phone, '91') ? $phone : '91' . $phone) : '';
        return "sms:{$phonePart}?body={$text}";
    }

    /**
     * Get default subject for quotation email.
     */
    public function getEmailSubject(): string
    {
        $companyName = config('app.name', 'CCTV Security CRM');
        return "Official CCTV Quotation #{$this->quotation_no} - {$companyName}";
    }

    /**
     * Get clean plain-text formatted proposal message suitable for email bodies.
     */
    public function getEmailFormattedBody(): string
    {
        $leadName = $this->lead?->customer_name ?? 'Valued Customer';
        $companyName = config('app.name', 'Precision IT Systems');
        $validUntil = $this->valid_until ? $this->valid_until->format('d M Y') : '15 days from issue';
        $quoteDate = ($this->quotation_date ?? $this->created_at ?? now())->format('d M Y');

        $itemsSummary = "";
        if ($this->items->isNotEmpty()) {
            foreach ($this->items as $item) {
                $productName = $item->item_name ?: ($item->product ? $item->product->name : ($item->description ?: 'CCTV Equipment'));
                $qty = number_format((float)$item->quantity, 0);
                $total = number_format((float)$item->total, 2);
                $itemsSummary .= "- {$qty}x {$productName} (₹{$total})\n";
            }
        }

        $pdfUrl = route('quotations.public-pdf', ['quotation' => $this, 'format' => '1']);
        $payUrl = route('payment.checkout.quotation', $this);

        $body = "Dear {$leadName},\n\n"
              . "Thank you for contacting us regarding your CCTV surveillance and security requirements.\n"
              . "Please find below the official quotation and pricing summary for your installation:\n\n"
              . "Quotation Number: #{$this->quotation_no}\n"
              . "Date: {$quoteDate}\n"
              . "Valid Until: {$validUntil}\n\n";

        if (!empty($itemsSummary)) {
            $body .= "Equipment & Services Summary:\n{$itemsSummary}\n";
        }

        $body .= "Subtotal: ₹" . number_format((float)$this->subtotal, 2) . "\n";
        if ((float)$this->discount > 0) {
            $body .= "Special Discount: -₹" . number_format((float)$this->discount, 2) . "\n";
        }
        if ((float)$this->tax_amount > 0) {
            $taxPercent = number_format((float)($this->tax_percent ?? 18), 0);
            $body .= "GST ({$taxPercent}%): ₹" . number_format((float)$this->tax_amount, 2) . "\n";
        }
        $body .= "Grand Total: ₹" . number_format((float)$this->total, 2) . "\n\n"
              . "==============================================\n"
              . "📄 View & Download Complete PDF Proposal:\n{$pdfUrl}\n\n"
              . "💳 Pay 50% Advance Online (Razorpay / UPI):\n{$payUrl}\n"
              . "==============================================\n\n"
              . "Please feel free to reply directly to this email or call us if you have any questions.\n\n"
              . "Best Regards,\n"
              . "Engineering & Sales Team\n"
              . "{$companyName}";

        return $body;
    }

    /**
     * Get Microsoft Outlook Web compose URL (Office 365 / Outlook.com).
     */
    public function getOutlookComposeUrl(): string
    {
        $to = $this->lead?->email ?? '';
        $subject = rawurlencode($this->getEmailSubject());
        $body = rawurlencode($this->getEmailFormattedBody());

        return "https://outlook.office.com/mail/deeplink/compose?to=" . rawurlencode($to) . "&subject={$subject}&body={$body}";
    }

    /**
     * Get system default mailto: URL for Desktop Outlook / Windows Mail.
     */
    public function getMailtoUrl(): string
    {
        $to = $this->lead?->email ?? '';
        $subject = rawurlencode($this->getEmailSubject());
        $body = rawurlencode($this->getEmailFormattedBody());

        return "mailto:{$to}?subject={$subject}&body={$body}";
    }
}