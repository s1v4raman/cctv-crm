<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class Quotation extends Model
{
    protected $fillable = [
        'lead_id',
        'created_by',
        'approved_by',
        'approved_at',
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
        'pdf_format',
        'pricing_mode',
    ];

    public const FORMATS = [
        '1' => 'Executive Modern',
        '2' => 'Technical Detailed',
        '3' => 'Linepix GST Invoice',
    ];

    public const PRICING_MODES = [
        'itemized'   => 'With Product Cost & Total',
        'total_only' => 'Total Cost Only (Lump Sum)',
    ];

    public function getPdfFormatNameAttribute(): string
    {
        return self::FORMATS[$this->pdf_format ?? '1'] ?? 'Executive Modern';
    }

    public function getPricingModeNameAttribute(): string
    {
        return self::PRICING_MODES[$this->pricing_mode ?? 'itemized'] ?? 'With Product Cost & Total';
    }

    public function isTotalCostOnly(): bool
    {
        return ($this->pricing_mode ?? 'itemized') === 'total_only';
    }

    /**
     * Get dynamic, financial-year based reference number according to template format.
     * Examples for FY 2026-27:
     * - Format 1: PITS/QUOT/26-27/02
     * - Format 2: NP/26-27/002
     * - Format 3: LP/26-27/02
     */
    public function getFormattedReferenceNo(?string $format = '1'): string
    {
        $raw = (string)($this->quotation_no ?? '');

        // If quotation already has an explicit custom formatted string matching the prefix, preserve it
        if ($format === '3' && str_starts_with($raw, 'LP/')) return $raw;
        if ($format === '2' && str_starts_with($raw, 'NP/')) return $raw;
        if ($format === '1' && str_starts_with($raw, 'PITS/')) return $raw;

        $quoteDate = $this->quotation_date ?? $this->created_at ?? now();
        $carbon = $quoteDate instanceof \Carbon\Carbon ? $quoteDate : \Carbon\Carbon::parse($quoteDate);

        $year = (int)$carbon->format('Y');
        $month = (int)$carbon->format('n');
        $fyStart = $month >= 4 ? substr((string)$year, -2) : substr((string)($year - 1), -2);
        $fyEnd   = $month >= 4 ? substr((string)($year + 1), -2) : substr((string)$year, -2);
        $fy = "{$fyStart}-{$fyEnd}"; // e.g. "26-27"

        // Extract sequential numeric ID or suffix
        $seq = 1;
        if (preg_match('/(\d+)$/', $raw, $matches)) {
            $seq = (int)$matches[1];
        } elseif ($this->id) {
            $seq = (int)$this->id;
        }

        return match ($format) {
            '2' => "NP/{$fy}/" . str_pad((string)$seq, 3, '0', STR_PAD_LEFT),
            '3', '4' => "LP/{$fy}/" . str_pad((string)$seq, 2, '0', STR_PAD_LEFT),
            default => "PITS/QUOT/{$fy}/" . str_pad((string)$seq, 2, '0', STR_PAD_LEFT),
        };
    }

    protected function casts(): array
    {
        return [
            'quotation_date' => 'date',
            'valid_until' => 'date',
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
            'rejected_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function isApproved(): bool
    {
        return in_array($this->status, ['approved', 'sent', 'accepted']);
    }

    public function canSendToCustomer(): bool
    {
        return in_array($this->status, ['approved', 'sent', 'accepted']);
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

    public static function indianRupeesWords(float|int|string|null $amount): string
    {
        $amount = round((float) $amount, 2);
        if ($amount <= 0) {
            return 'Zero Rupees only';
        }

        $crore = floor($amount / 10000000);
        $amount -= $crore * 10000000;
        $lakh = floor($amount / 100000);
        $amount -= $lakh * 100000;
        $thousand = floor($amount / 1000);
        $amount -= $thousand * 1000;
        $hundred = floor($amount / 100);
        $amount -= $hundred * 100;
        $tens = floor($amount);
        $paise = round(($amount - $tens) * 100);

        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
            'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tensArr = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        $toWords = function ($n) use ($ones, $tensArr) {
            if ($n < 20) {
                return $ones[$n];
            }
            $t = $tensArr[intdiv($n, 10)];
            $u = $ones[$n % 10];
            return $u ? "$t $u" : $t;
        };

        $parts = [];
        if ($crore > 0) {
            $parts[] = $toWords($crore) . ' Crore';
        }
        if ($lakh > 0) {
            $parts[] = $toWords($lakh) . ' Lakh';
        }
        if ($thousand > 0) {
            $parts[] = $toWords($thousand) . ' Thousand';
        }
        if ($hundred > 0) {
            $parts[] = $toWords($hundred) . ' Hundred';
        }
        if ($tens > 0) {
            $parts[] = $toWords($tens);
        }

        $res = implode(' ', $parts) . ' Rupees';
        if ($paise > 0) {
            $res .= ' and ' . $toWords($paise) . ' Paise';
        }

        return trim($res) . ' only';
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
    public function getWhatsAppFormattedMessage(string $format = '1', ?string $pricingMode = null): string
    {
        $leadName = $this->lead?->customer_name ?? 'Valued Customer';
        $companyName = config('app.name', 'Precision IT Systems');
        $validUntil = $this->valid_until ? $this->valid_until->format('d M Y') : '15 days from issue';
        $quoteDate = ($this->quotation_date ?? $this->created_at ?? now())->format('d M Y');

        $formatName = match($format) {
            '2' => 'Technical Detailed Quotation',
            '3', '4' => 'Linepix GST Invoice',
            default => 'Executive Modern Quotation',
        };

        $isTotalOnly = $pricingMode !== null ? ($pricingMode === 'total_only') : $this->isTotalCostOnly();

        $itemsSummary = "";
        if ($isTotalOnly) {
            $itemsSummary = "• Complete Turnkey CCTV Surveillance & Security Infrastructure Solution\n  (Complete Supply, Cabling, Hardware Installation, Integration & Commissioning)\n";
        } elseif ($this->items->isNotEmpty()) {
            foreach ($this->items as $item) {
                $productName = $item->item_name ?: ($item->product ? $item->product->name : ($item->description ?: 'CCTV Equipment'));
                $qty = number_format((float)$item->quantity, 0);
                $total = number_format((float)$item->total, 2);
                $itemsSummary .= "• {$qty}x {$productName} - ₹{$total}\n";
            }
        }

        $pdfUrl = URL::signedRoute('quotations.public-pdf', ['quotation' => $this, 'format' => $format]);

        $msg ="📹 CCTV INSTALLATION & SECURITY PROPOSAL\n"
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
             . "📎 *Quotation PDF Document Attached*\n"
             . "Please review the attached official quotation proposal. Reply directly to this chat for any questions or approval.\n\n"
             . "Best Regards,\n"
             . "*{$companyName}*";

        return $msg;
    }

    /**
     * Get direct WhatsApp Link (Universal Web & Mobile App Dispatcher).
     */
    public function getWhatsAppUrl(string $format = '1', ?string $customPhone = null, ?string $customMsg = null, ?string $pricingMode = null): string
    {
        $phone = $this->getWhatsAppPhone($customPhone);
        $text = rawurlencode($customMsg ?: $this->getWhatsAppFormattedMessage($format, $pricingMode));

        if (empty($phone)) {
            return "https://api.whatsapp.com/send?text={$text}";
        }

        return "https://api.whatsapp.com/send?phone={$phone}&text={$text}";
    }

    /**
     * Get WhatsApp Web Direct Link (web.whatsapp.com).
     */
    public function getWhatsAppWebUrl(string $format = '1', ?string $customPhone = null, ?string $customMsg = null, ?string $pricingMode = null): string
    {
        $phone = $this->getWhatsAppPhone($customPhone);
        $text = rawurlencode($customMsg ?: $this->getWhatsAppFormattedMessage($format, $pricingMode));

        if (empty($phone)) {
            return "https://web.whatsapp.com/send?text={$text}";
        }

        return "https://web.whatsapp.com/send?phone={$phone}&text={$text}";
    }

    /**
     * Get direct WhatsApp Desktop scheme (whatsapp://).
     */
    public function getWhatsAppAppUrl(string $format = '1', ?string $customPhone = null, ?string $customMsg = null, ?string $pricingMode = null): string
    {
        $phone = $this->getWhatsAppPhone($customPhone);
        $text = rawurlencode($customMsg ?: $this->getWhatsAppFormattedMessage($format, $pricingMode));

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
        $pdfUrl = URL::signedRoute('quotations.public-pdf', ['quotation' => $this, 'format' => $format]);
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
    public function getEmailFormattedBody(?string $format = '1', ?string $pricingMode = 'itemized', ?string $customNote = null): string
    {
        $leadName = $this->lead?->customer_name ?? 'Valued Customer';
        $companyName = config('app.name', 'Precision IT Systems');
        $validUntil = $this->valid_until ? $this->valid_until->format('d M Y') : '15 days from issue';
        $quoteDate = ($this->quotation_date ?? $this->created_at ?? now())->format('d M Y');

        $formatName = match($format) {
            '2' => 'Technical Detailed (Format 2)',
            '3', '4' => 'Linepix GST Invoice (Format 3)',
            default => 'Executive Modern (Format 1)',
        };

        $itemsSummary = "";
        if ($pricingMode !== 'total_only' && $this->items->isNotEmpty()) {
            foreach ($this->items as $item) {
                $productName = $item->item_name ?: ($item->product ? $item->product->name : ($item->description ?: 'CCTV Equipment'));
                $qty = number_format((float)$item->quantity, 0);
                $total = number_format((float)$item->total, 2);
                $itemsSummary .= "- {$qty}x {$productName} (₹{$total})\n";
            }
        } elseif ($pricingMode === 'total_only' && $this->items->isNotEmpty()) {
            foreach ($this->items as $item) {
                $productName = $item->item_name ?: ($item->product ? $item->product->name : ($item->description ?: 'CCTV Equipment'));
                $qty = number_format((float)$item->quantity, 0);
                $itemsSummary .= "- {$qty}x {$productName}\n";
            }
        }

        $pdfUrl = URL::signedRoute('quotations.public-pdf', ['quotation' => $this, 'format' => $format ?: '1', 'pricing_mode' => $pricingMode ?: 'itemized']);

        $body = "Dear {$leadName},\n\n"
              . "Thank you for contacting us regarding your CCTV surveillance and security requirements.\n"
              . "Please find below the official quotation and pricing summary for your installation:\n\n"
              . "Quotation Number: #{$this->quotation_no}\n"
              . "Date: {$quoteDate}\n"
              . "Proposal Style: {$formatName}\n"
              . "Valid Until: {$validUntil}\n\n";

        if (!empty($customNote)) {
            $body .= "Note from Sales / Engineering Team:\n{$customNote}\n\n";
        }

        if (!empty($itemsSummary)) {
            $body .= ($pricingMode === 'total_only' ? "Scope of Equipment & Services:\n" : "Equipment & Services Summary:\n") . "{$itemsSummary}\n";
        }

        if ($pricingMode !== 'total_only') {
            $body .= "Subtotal: ₹" . number_format((float)$this->subtotal, 2) . "\n";
            if ((float)$this->discount > 0) {
                $body .= "Special Discount: -₹" . number_format((float)$this->discount, 2) . "\n";
            }
            if ((float)$this->tax_amount > 0) {
                $taxPercent = number_format((float)($this->tax_percent ?? 18), 0);
                $body .= "GST ({$taxPercent}%): ₹" . number_format((float)$this->tax_amount, 2) . "\n";
            }
        }
        $body .= "Grand Total: ₹" . number_format((float)$this->total, 2) . "\n\n"
              . "==============================================\n"
              . "📄 View & Download Complete PDF Proposal:\n{$pdfUrl}\n"
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
    public function getOutlookComposeUrl(?string $format = '1', ?string $pricingMode = 'itemized', ?string $customNote = null): string
    {
        $to = $this->lead?->email ?? '';
        $subject = rawurlencode($this->getEmailSubject());
        $body = rawurlencode($this->getEmailFormattedBody($format, $pricingMode, $customNote));

        return "https://outlook.office.com/mail/deeplink/compose?to=" . rawurlencode($to) . "&subject={$subject}&body={$body}";
    }

    /**
     * Get Google Gmail Web compose URL.
     */
    public function getGmailComposeUrl(?string $format = '1', ?string $pricingMode = 'itemized', ?string $customNote = null): string
    {
        $to = $this->lead?->email ?? '';
        $subject = rawurlencode($this->getEmailSubject());
        $body = rawurlencode($this->getEmailFormattedBody($format, $pricingMode, $customNote));

        return "https://mail.google.com/mail/?view=cm&fs=1&to=" . rawurlencode($to) . "&su={$subject}&body={$body}";
    }

    /**
     * Get system default mailto: URL for Desktop Outlook / Windows Mail.
     */
    public function getMailtoUrl(?string $format = '1', ?string $pricingMode = 'itemized', ?string $customNote = null): string
    {
        $to = $this->lead?->email ?? '';
        $subject = rawurlencode($this->getEmailSubject());
        $body = rawurlencode($this->getEmailFormattedBody($format, $pricingMode, $customNote));

        return "mailto:{$to}?subject={$subject}&body={$body}";
    }
}