<?php

namespace App\Http\Controllers;

use App\Helpers\GstHelper;
use App\Http\Requests\StoreQuotationRequest;
use App\Models\GatewaySetting;
use App\Models\Lead;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Supplier;
use App\Services\DocumentSequenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotationController extends Controller
{
    public function create(Lead $lead): View
    {
        $suppliers = \App\Models\Supplier::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'company_name', 'phone', 'categories', 'brands_supplied']);

        return view('quotations.create', compact('lead', 'suppliers'));
    }

    public function createGeneral(): View
    {
        $leads = Lead::orderBy('customer_name')->get();
        return view('quotations.select_lead', compact('leads'));
    }

    public function store(StoreQuotationRequest $request, Lead $lead): RedirectResponse
    {
        $data = $request->validated();
        $isTech = Auth::user()?->isTechnician();

        $quotation = DB::transaction(function () use ($data, $lead, $isTech) {
            $subtotal = 0;

            // Fetch catalog product prices if technician or employee submitted items without price or missing product_id
            $processedItems = [];
            foreach ($data['items'] as $item) {
                $qty = (float) $item['quantity'];
                $price = isset($item['unit_price']) && is_numeric($item['unit_price']) ? (float) $item['unit_price'] : 0;
                
                $catalogProduct = null;
                if (!empty($item['product_id'])) {
                    $catalogProduct = \App\Models\Product::find($item['product_id']);
                }
                if (!$catalogProduct && !empty($item['item_name'])) {
                    $catalogProduct = \App\Models\Product::where('name', trim($item['item_name']))
                        ->orWhere('sku', trim($item['item_name']))
                        ->first();
                    if ($catalogProduct) {
                        $item['product_id'] = $catalogProduct->id;
                    }
                }

                if ($price <= 0 && $catalogProduct && (float) $catalogProduct->unit_price > 0) {
                    $price = (float) $catalogProduct->unit_price;
                }
                $item['unit_price'] = $price;
                $lineTotal = $qty * $price;
                $subtotal += $lineTotal;

                $processedItems[] = [
                    'item' => $item,
                    'line_total' => $lineTotal,
                ];
            }

            $requestedDiscount = (float) ($data['discount'] ?? 0);
            $discount = min($requestedDiscount, $subtotal);

            $taxPercent = (float) ($data['tax_percent'] ?? 18);
            $taxableAmount = $subtotal - $discount;
            $taxAmount = $taxableAmount * ($taxPercent / 100);
            $grandTotal = $taxableAmount + $taxAmount;

            $status = $isTech ? 'pending_approval' : 'draft';

            $quotation = Quotation::create([
                'lead_id' => $lead->id,
                'created_by' => Auth::id(),
                'quotation_no' => 'QT-' . now()->format('Ymd') . '-' . str_pad(
                    (string) (Quotation::count() + 1),
                    4,
                    '0',
                    STR_PAD_LEFT
                ),
                'quotation_date' => $data['quotation_date'],
                'valid_until' => $data['valid_until'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax_percent' => $taxPercent,
                'tax_amount' => $taxAmount,
                'total' => $grandTotal,
                'status' => $status,
                'notes' => $data['notes'] ?? null,
                'pdf_format' => in_array($data['pdf_format'] ?? '1', ['1', '2', '3', '4']) ? $data['pdf_format'] : '1',
                'pricing_mode' => $data['pricing_mode'] ?? 'itemized',
            ]);

            foreach ($processedItems as $entry) {
                $item = $entry['item'];
                $quotation->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => (float) $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_price' => (float) $item['unit_price'],
                    'total' => $entry['line_total'],
                ]);
            }

            $lead->update([
                'status' => 'quoted',
            ]);

            $quotation->statusHistories()->create([
                'changed_by' => Auth::id(),
                'from_status' => null,
                'to_status' => $status,
                'notes' => $isTech
                    ? 'Field technician ' . Auth::user()->name . ' created quotation with ' . count($processedItems) . ' items with on-site pricing (Grand Total: ' . \App\Support\Inr::money($grandTotal) . '). Awaiting Admin review.'
                    : 'Quotation created.',
            ]);

            return $quotation;
        });

        // Notify Admin & Employee teams immediately if technician submitted scope
        if ($isTech) {
            \App\Services\HeaderNotificationService::notifyAdminAndStaffOfQuotation($quotation, Auth::user());

            return redirect()
                ->route('technician.dashboard')
                ->with('status', "Quotation #{$quotation->quotation_no} submitted successfully! On-site pricing recorded (Grand Total: " . \App\Support\Inr::money($quotation->total) . "). Forwarded to Admin for review.");
        }

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('status', "Quotation #{$quotation->quotation_no} created successfully.");
    }

    public function show(Quotation $quotation): View
    {
        $quotation->load([
            'lead',
            'items',
            'installationJob',
            'statusHistories.changedBy',
        ]);

        $whatsappMonthlyCount = \App\Models\NotificationLog::where('channel', 'whatsapp')
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        return view('quotations.show', compact('quotation', 'whatsappMonthlyCount'));
    }

    public function previewPage(Request $request, Quotation $quotation, ?string $format = null): View
    {
        $quotation->load([
            'lead',
            'items.product',
        ]);

        if (!$format || !in_array($format, ['1', '2', '3'])) {
            $format = in_array($quotation->pdf_format ?? '1', ['1', '2', '3']) ? $quotation->pdf_format : '1';
        }

        $pricingMode = $request->query('pricing_mode', $quotation->pricing_mode ?? 'itemized');
        if (!in_array($pricingMode, ['itemized', 'total_only'])) {
            $pricingMode = 'itemized';
        }

        return view('quotations.preview', compact('quotation', 'format', 'pricingMode'));
    }

    public function downloadPdf(Request $request, Quotation $quotation, ?string $format = null)
    {
        if (Auth::user()?->isTechnician() && $quotation->status === 'pending_approval') {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('error', 'Admin review & approval is required before downloading or sharing this quotation.');
        }

        $quotation->load(['lead', 'items']);

        if (!$format || !in_array($format, ['1', '2', '3', '4'])) {
            $format = in_array($quotation->pdf_format ?? '1', ['1', '2', '3', '4']) ? $quotation->pdf_format : '1';
        }

        $pricingMode = $request->query('pricing_mode', $quotation->pricing_mode ?? 'itemized');
        if (in_array($pricingMode, ['itemized', 'total_only'])) {
            $quotation->pricing_mode = $pricingMode;
        }

        $pdf = Pdf::loadView('quotations.pdf.format' . $format, compact('quotation'));

        $safeCustomer = \Illuminate\Support\Str::slug($quotation->lead?->customer_name ?? 'customer', '_');
        $filename = 'Quotation_' . $quotation->quotation_no . ($safeCustomer ? '_' . $safeCustomer : '') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Generate and stream a standard RFC-822 (.eml) draft file with embedded Base64 PDF attachment.
     * When opened in Windows, Outlook immediately opens the message in Compose mode with the PDF attached!
     */
    public function downloadEml(Request $request, Quotation $quotation)
    {
        if (Auth::user()?->isTechnician() && $quotation->status === 'pending_approval') {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('error', 'Admin review & approval is required before downloading or sharing this quotation.');
        }

        $quotation->loadMissing(['lead', 'items.product']);

        $format = $request->query('pdf_format', $quotation->pdf_format ?? '1');
        if (!in_array((string) $format, ['1', '2', '3', '4'])) {
            $format = '1';
        }

        $pricingMode = $request->query('pricing_mode', $quotation->pricing_mode ?? 'itemized');
        if (!in_array((string) $pricingMode, ['itemized', 'total_only'])) {
            $pricingMode = 'itemized';
        }

        $quotation->pricing_mode = $pricingMode;

        // Compile Quotation PDF
        $pdf = Pdf::loadView('quotations.pdf.format' . $format, compact('quotation'));
        $pdfContent = $pdf->output();

        $recipientEmail = trim((string) $request->query('email', ''));
        if ($recipientEmail === '') {
            $recipientEmail = trim((string) ($quotation->lead?->email ?? $quotation->customer_email ?? ''));
        }
        $customerName = trim((string) ($quotation->lead?->customer_name ?? $quotation->customer_name ?? 'Customer'));
        if ($customerName === '') {
            $customerName = 'Customer';
        }
        $subject        = trim((string) $request->query('subject', "Official Quotation #{$quotation->quotation_no} - CCTV Installation & Security Proposal"));
        $customNote     = trim((string) $request->query('custom_note', ''));

        $safeCustomer = \Illuminate\Support\Str::slug($customerName, '_');
        $pdfFilename = "Quotation_{$quotation->quotation_no}" . ($safeCustomer ? "_{$safeCustomer}" : '') . ".pdf";
        $emlFilename = "Draft_Quotation_{$quotation->quotation_no}" . ($safeCustomer ? "_{$safeCustomer}" : '') . ".eml";

        $dateFormatted = $quotation->quotation_date ? $quotation->quotation_date->format('d M Y') : now()->format('d M Y');
        $validUntilFormatted = $quotation->valid_until ? $quotation->valid_until->format('d M Y') : now()->addDays(15)->format('d M Y');
        $pricingModeLabel = $pricingMode === 'total_only' ? 'Turnkey Total Cost' : 'Itemized with Product Costs';

        // Plain text body - strictly proposal summary & notice that PDF is attached. NO download links!
        $plainBody = "Dear {$customerName},\r\n\r\n"
            . "Thank you for contacting Precision IT Systems regarding your CCTV surveillance and security requirements.\r\n"
            . "Please find attached your official quotation proposal and pricing schedule:\r\n\r\n"
            . "Quotation Number : #{$quotation->quotation_no}\r\n"
            . "Date             : {$dateFormatted}\r\n"
            . "Valid Until      : {$validUntilFormatted}\r\n"
            . "Pricing Format   : {$pricingModeLabel}\r\n"
            . "Grand Total      : " . \App\Support\Inr::money($quotation->total) . "\r\n\r\n";

        if ($customNote !== '') {
            $plainBody .= "Special Note from Engineering Team:\r\n{$customNote}\r\n\r\n";
        }

        $plainBody .= "The official signed PDF document ({$pdfFilename}) has been automatically attached to this email for your review and records.\r\n\r\n"
            . "Please feel free to reply directly to this email or contact us if you have any questions.\r\n\r\n"
            . "Best Regards,\r\n"
            . "Engineering & Sales Team\r\n"
            . "Precision IT Systems";

        // HTML version
        $htmlBody = "<div style=\"font-family: Arial, sans-serif; font-size: 14px; color: #1e293b; line-height: 1.6;\">"
            . "<p>Dear <strong>" . e($customerName) . "</strong>,</p>"
            . "<p>Thank you for contacting <strong>Precision IT Systems</strong> regarding your CCTV surveillance and security requirements. Please find attached your official quotation proposal and pricing schedule:</p>"
            . "<table style=\"border-collapse: collapse; margin: 16px 0; font-size: 13px;\">"
            . "<tr><td style=\"padding: 4px 12px 4px 0; color: #64748b;\">Quotation Number:</td><td><strong>#" . e($quotation->quotation_no) . "</strong></td></tr>"
            . "<tr><td style=\"padding: 4px 12px 4px 0; color: #64748b;\">Date:</td><td>" . e($dateFormatted) . "</td></tr>"
            . "<tr><td style=\"padding: 4px 12px 4px 0; color: #64748b;\">Valid Until:</td><td>" . e($validUntilFormatted) . "</td></tr>"
            . "<tr><td style=\"padding: 4px 12px 4px 0; color: #64748b;\">Grand Total:</td><td><strong style=\"color: #2563eb; font-size: 15px;\">" . \App\Support\Inr::money($quotation->total) . "</strong></td></tr>"
            . "</table>";

        if ($customNote !== '') {
            $htmlBody .= "<div style=\"background-color: #f8fafc; border-left: 4px solid #3b82f6; padding: 10px 14px; margin: 16px 0;\">"
                . "<strong>Special Note:</strong><br>" . nl2br(e($customNote)) . "</div>";
        }

        $htmlBody .= "<p style=\"background: #ecfdf5; border: 1px solid #a7f3d0; padding: 10px 14px; border-radius: 6px; color: #065f46; font-weight: bold;\">"
            . "📎 The signed proposal document (<strong>" . e($pdfFilename) . "</strong>) is attached to this email."
            . "</p>"
            . "<p>Please feel free to reply directly to this email or call us if you have any questions.</p>"
            . "<p>Best Regards,<br><strong>Engineering &amp; Sales Team</strong><br>Precision IT Systems</p>"
            . "</div>";

        $boundaryMixed = '=_MixedBoundary_' . md5(uniqid((string) time(), true));
        $boundaryAlt   = '=_AltBoundary_' . md5(uniqid((string) (time() + 1), true));

        $fromAddress = config('mail.from.address', 'sales@cctvcrm.com');
        $fromName    = config('mail.from.name', 'Precision IT Systems');

        $eml = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromAddress}>\r\n";
        if ($recipientEmail !== '') {
            if ($customerName !== 'Customer') {
                $eml .= "To: =?UTF-8?B?" . base64_encode($customerName) . "?= <{$recipientEmail}>\r\n";
            } else {
                $eml .= "To: <{$recipientEmail}>\r\n";
            }
        }
        $eml .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $eml .= "Date: " . date('r') . "\r\n";
        $eml .= "MIME-Version: 1.0\r\n";
        $eml .= "X-Unsent: 1\r\n"; // Informs Outlook / Windows Mail to open as an unsent draft in compose mode!
        $eml .= "Content-Type: multipart/mixed; boundary=\"{$boundaryMixed}\"\r\n\r\n";

        // Body section (multipart/alternative)
        $eml .= "--{$boundaryMixed}\r\n";
        $eml .= "Content-Type: multipart/alternative; boundary=\"{$boundaryAlt}\"\r\n\r\n";

        // Plain text part
        $eml .= "--{$boundaryAlt}\r\n";
        $eml .= "Content-Type: text/plain; charset=UTF-8; format=flowed\r\n";
        $eml .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $eml .= $plainBody . "\r\n\r\n";

        // HTML part
        $eml .= "--{$boundaryAlt}\r\n";
        $eml .= "Content-Type: text/html; charset=UTF-8\r\n";
        $eml .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
        $eml .= $htmlBody . "\r\n\r\n";
        $eml .= "--{$boundaryAlt}--\r\n\r\n";

        // Attachment part
        $eml .= "--{$boundaryMixed}\r\n";
        $eml .= "Content-Type: application/pdf; name=\"{$pdfFilename}\"\r\n";
        $eml .= "Content-Disposition: attachment; filename=\"{$pdfFilename}\"\r\n";
        $eml .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $eml .= chunk_split(base64_encode($pdfContent)) . "\r\n";
        $eml .= "--{$boundaryMixed}--\r\n";

        return response($eml, 200, [
            'Content-Type'        => 'message/rfc822',
            'Content-Disposition' => 'attachment; filename="' . $emlFilename . '"',
            'Cache-Control'       => 'no-cache, no-store, must-revalidate',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }

    public function publicPdf(Request $request, Quotation $quotation, ?string $format = '1')
    {
        $quotation->load(['lead', 'items']);

        if (!in_array($format, ['1', '2', '3', '4'])) {
            $format = in_array($quotation->pdf_format ?? '1', ['1', '2', '3', '4']) ? $quotation->pdf_format : '1';
        }

        if (!in_array($format, ['1', '2', '3', '4'])) {
            $format = '1';
        }

        $pricingMode = $request->query('pricing_mode', $quotation->pricing_mode ?? 'itemized');
        if (in_array($pricingMode, ['itemized', 'total_only'])) {
            $quotation->pricing_mode = $pricingMode;
        }

        $pdf = Pdf::loadView('quotations.pdf.format' . $format, compact('quotation'));

        $safeCustomer = \Illuminate\Support\Str::slug($quotation->lead?->customer_name ?? 'customer', '_');
        $filename = 'Quotation_' . $quotation->quotation_no . ($safeCustomer ? '_' . $safeCustomer : '') . '_format' . $format . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Dispatch and automatically open WhatsApp with the customer's phone number and quotation summary.
     */
    public function sendWhatsApp(Request $request, Quotation $quotation)
    {
        $quotation->load(['lead', 'items.product']);

        $format = $request->input('format', '1');
        if (!in_array($format, ['1', '2', '3', '4'])) {
            $format = '1';
        }

        $pricingMode = $request->input('pricing_mode', $quotation->pricing_mode ?? 'itemized');
        if (!in_array($pricingMode, ['itemized', 'total_only'])) {
            $pricingMode = 'itemized';
        }

        if ($quotation->pdf_format !== $format || $quotation->pricing_mode !== $pricingMode) {
            $quotation->update([
                'pdf_format'   => $format,
                'pricing_mode' => $pricingMode,
            ]);
        }

        $customPhone = $request->input('phone');
        $customName = $request->input('customer_name');

        if ($quotation->lead) {
            $updates = [];
            if (!empty($customPhone) && $quotation->lead->phone !== $customPhone) {
                $updates['phone'] = $customPhone;
            }
            if (!empty($customName) && $quotation->lead->customer_name !== $customName) {
                $updates['customer_name'] = $customName;
            }
            if (!empty($updates)) {
                $quotation->lead->update($updates);
                $quotation->load('lead');
            }
        } elseif (!empty($customName) || !empty($customPhone)) {
            // Automatically add new customer to contacts (Lead model) if quotation had no lead
            $lead = \App\Models\Lead::create([
                'customer_name' => $customName ?: 'Customer',
                'phone'         => $customPhone ?: '',
                'source'        => 'quotation',
                'status'        => 'contacted',
                'created_by'    => Auth::id(),
            ]);
            $quotation->update(['lead_id' => $lead->id]);
            $quotation->load('lead');
        }

        if ($quotation->status === 'pending_approval' && Auth::user()->isTechnician()) {
            return back()->with('error', 'This quotation is pending Admin pricing and product approval. Once approved, you will be able to share it with the customer.');
        }

        $customMsg = $request->input('custom_message');
        $phone = $quotation->getWhatsAppPhone($customPhone);
        $message = $customMsg ?: $quotation->getWhatsAppFormattedMessage($format, $pricingMode);

        if (in_array($quotation->status, ['draft', 'approved'])) {
            $prevStatus = $quotation->status;
            $quotation->update([
                'status'  => 'sent',
                'sent_at' => now(),
            ]);

            $quotation->statusHistories()->create([
                'changed_by'  => Auth::id(),
                'from_status' => $prevStatus,
                'to_status'   => 'sent',
                'notes'       => "Quotation PDF (Format {$format}, {$pricingMode}) shared with customer via WhatsApp (" . ($phone ?: 'No Phone') . ").",
            ]);
        } else {
            $quotation->statusHistories()->create([
                'changed_by'  => Auth::id(),
                'from_status' => $quotation->status,
                'to_status'   => $quotation->status,
                'notes'       => "Quotation PDF (Format {$format}, {$pricingMode}) re-shared with customer via WhatsApp (" . ($phone ?: 'No Phone') . ").",
            ]);
        }

        // Record in Notification Log for full auditability
        try {
            \App\Models\NotificationLog::create([
                'channel'         => 'whatsapp',
                'event_type'      => 'quotation_sent',
                'recipient_type'  => 'customer',
                'recipient_name'  => $quotation->lead?->customer_name ?? 'Customer',
                'recipient_phone' => $phone,
                'recipient_email' => $quotation->lead?->email,
                'subject'         => "Quotation #{$quotation->quotation_no} (Format {$format})",
                'message_body'    => $message,
                'action_url'      => URL::signedRoute('quotations.public-pdf', ['quotation' => $quotation, 'format' => $format, 'pricing_mode' => $pricingMode]),
                'status'          => 'sent',
                'reference_type'  => get_class($quotation),
                'reference_id'    => $quotation->id,
                'sent_at'         => now(),
                'created_by'      => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Quotation WhatsApp NotificationLog write failed: " . $e->getMessage());
        }

        $whatsappUrl = $quotation->getWhatsAppUrl($format, $customPhone, $customMsg, $pricingMode);
        $webUrl = $quotation->getWhatsAppWebUrl($format, $customPhone, $customMsg, $pricingMode);
        $appUrl = $quotation->getWhatsAppAppUrl($format, $customPhone, $customMsg, $pricingMode);
        $smsUrl = $quotation->getSmsUrl($format, $customPhone, $customMsg);
        $pdfUrl = route('quotations.pdf', ['quotation' => $quotation, 'format' => $format, 'pricing_mode' => $pricingMode]);
        $vcardUrl = route('quotations.vcard', $quotation);

        if ($request->expectsJson() || $request->ajax()) {
            $monthlyCount = \App\Models\NotificationLog::where('channel', 'whatsapp')
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count();

            return response()->json([
                'success'        => true,
                'whatsapp_url'   => $whatsappUrl,
                'web_url'        => $webUrl,
                'app_url'        => $appUrl,
                'sms_url'        => $smsUrl,
                'pdf_url'        => $pdfUrl,
                'vcard_url'      => $vcardUrl,
                'format'         => $format,
                'phone'          => $phone,
                'customer_name'  => $quotation->lead?->customer_name ?? 'Customer',
                'message'        => $message,
                'is_saved'       => !empty($phone),
                'monthly_count'  => $monthlyCount,
            ]);
        }

        return redirect()->away($whatsappUrl);
    }

    /**
     * Dispatch Quotation PDF Document directly into customer WhatsApp chat via Cloud API.
     * Meta Policy: First 1,000 non-template messages inside 24h window are free (₹0).
     */
    public function sendWhatsAppDocument(Request $request, Quotation $quotation): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'phone'          => ['required', 'string'],
            'customer_name'  => ['nullable', 'string'],
            'format'         => ['nullable', 'in:1,2,3,4'],
            'pricing_mode'   => ['nullable', 'in:itemized,total_only'],
            'custom_message' => ['nullable', 'string', 'max:1000'],
        ]);

        $phone = $validated['phone'];
        $format = $validated['format'] ?? $quotation->pdf_format ?? '1';
        $pricingMode = $validated['pricing_mode'] ?? $quotation->pricing_mode ?? 'itemized';
        $caption = $validated['custom_message'] ?? null;

        // Auto-save phone to customer profile if provided/updated
        if ($quotation->lead && !empty($phone)) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            if ($quotation->lead->phone !== $cleanPhone && $quotation->lead->phone !== $phone) {
                $quotation->lead->update(['phone' => $phone]);
            }
            if (!empty($validated['customer_name']) && $quotation->lead->customer_name !== $validated['customer_name']) {
                $quotation->lead->update(['customer_name' => $validated['customer_name']]);
            }
        }

        $result = \App\Services\WhatsAppCloudService::sendQuotationDocument($quotation, $phone, $caption, $format, $pricingMode);

        $monthlyCount = \App\Models\NotificationLog::where('channel', 'whatsapp')
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        return response()->json([
            'success'        => $result['success'],
            'error'          => $result['error'],
            'message_id'     => $result['message_id'],
            'requires_setup' => $result['requires_setup'],
            'rate_used'      => 'First 1,000 non-template messages per business number each month are free',
            'cost'           => '₹0',
            'window'         => 'Inside 24-hour window (after customer messaged first)',
            'monthly_count'  => $monthlyCount,
        ]);
    }

    /**
     * Dispatch Quotation Email with automatic PDF attachment.
     *
     * @return array{success: bool, error: ?string}
     */
    public function dispatchQuotationEmail(
        Quotation $quotation,
        string $recipientEmail,
        ?string $subject = null,
        ?string $customMessage = null,
        ?string $pdfFormat = null,
        ?string $pricingMode = null
    ): array {
        $pdfFormat = $pdfFormat ?: ($quotation->pdf_format ?? '1');
        $pricingMode = $pricingMode ?: ($quotation->pricing_mode ?? 'itemized');

        // Persist any updated format or pricing mode selected
        if ($quotation->pdf_format !== $pdfFormat || $quotation->pricing_mode !== $pricingMode) {
            $quotation->update([
                'pdf_format'   => $pdfFormat,
                'pricing_mode' => $pricingMode,
            ]);
        }

        // 1. Synchronize & persist customer email to Lead record if empty or updated
        if ($quotation->lead && ($quotation->lead->email !== $recipientEmail)) {
            $quotation->lead->update(['email' => $recipientEmail]);
        }

        // 2. Apply Dynamic SMTP Configuration & Dispatch Email with Attached PDF
        \App\Services\MailConfigService::applyDynamicMailConfig();

        $isLiveSmtp = \App\Services\MailConfigService::isSmtpConfigured();

        if (!$isLiveSmtp) {
            // Write to log for local file audit/troubleshooting
            try {
                \Illuminate\Support\Facades\Mail::to($recipientEmail)
                    ->send(new \App\Mail\QuotationEmailWithPdf($quotation, $customMessage, $subject, $pdfFormat, $pricingMode));
            } catch (\Throwable $e) {
                // ignore local log driver errors
            }

            // Record in Notification Log as 'logged_only'
            \App\Models\NotificationLog::create([
                'channel'         => 'email',
                'event_type'      => 'quotation_sent',
                'recipient_type'  => 'customer',
                'recipient_name'  => $quotation->lead?->customer_name ?? 'Customer',
                'recipient_phone' => $quotation->lead?->phone,
                'recipient_email' => $recipientEmail,
                'subject'         => $subject ?: "Official Quotation #{$quotation->quotation_no}",
                'message_body'    => $customMessage ?: "Quotation #{$quotation->quotation_no} with PDF attached.",
                'action_url'      => URL::signedRoute('quotations.public-pdf', ['quotation' => $quotation, 'format' => $pdfFormat]),
                'status'          => 'failed',
                'error_message'   => 'Live SMTP server is not configured in Settings. Email was saved to local log instead of customer inbox.',
                'reference_type'  => get_class($quotation),
                'reference_id'    => $quotation->id,
                'sent_at'         => now(),
                'created_by'      => Auth::id(),
            ]);

            return [
                'success'     => false,
                'is_log_mode' => true,
                'error'       => 'Live SMTP is not configured in CRM Settings. Please configure SMTP in Settings > Alert Gateways, or use Gmail Web to dispatch directly from your browser.',
            ];
        }

        $emailStatus = 'sent';
        $errorMessage = null;

        try {
            \Illuminate\Support\Facades\Mail::to($recipientEmail)
                ->send(new \App\Mail\QuotationEmailWithPdf($quotation, $customMessage, $subject, $pdfFormat, $pricingMode));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Quotation email dispatch failed to {$recipientEmail}: " . $e->getMessage());
            $emailStatus = 'failed';
            $errorMessage = $e->getMessage();
        }

        // 3. Record in Notification Log for full auditability
        \App\Models\NotificationLog::create([
            'channel'         => 'email',
            'event_type'      => 'quotation_sent',
            'recipient_type'  => 'customer',
            'recipient_name'  => $quotation->lead?->customer_name ?? 'Customer',
            'recipient_phone' => $quotation->lead?->phone,
            'recipient_email' => $recipientEmail,
            'subject'         => $subject ?: "Official Quotation #{$quotation->quotation_no}",
            'message_body'    => $customMessage ?: "Quotation #{$quotation->quotation_no} with PDF attached emailed to customer.",
            'action_url'      => URL::signedRoute('quotations.public-pdf', ['quotation' => $quotation, 'format' => $pdfFormat]),
            'status'          => $emailStatus,
            'error_message'   => $errorMessage,
            'reference_type'  => get_class($quotation),
            'reference_id'    => $quotation->id,
            'sent_at'         => now(),
            'created_by'      => Auth::id(),
        ]);

        if ($emailStatus === 'sent') {
            // Update status to 'sent' if currently 'draft' or 'approved'
            if (in_array($quotation->status, ['draft', 'approved'])) {
                $prevStatus = $quotation->status;
                $quotation->update([
                    'status'  => 'sent',
                    'sent_at' => now(),
                ]);

                $quotation->statusHistories()->create([
                    'changed_by'  => Auth::id(),
                    'from_status' => $prevStatus,
                    'to_status'   => 'sent',
                    'notes'       => "Quotation PDF automatically emailed to customer ({$recipientEmail}).",
                ]);
            } else {
                $quotation->statusHistories()->create([
                    'changed_by'  => Auth::id(),
                    'from_status' => $quotation->status,
                    'to_status'   => $quotation->status,
                    'notes'       => "Quotation PDF re-emailed to customer ({$recipientEmail}).",
                ]);
            }

            return ['success' => true, 'error' => null];
        }

        return ['success' => false, 'error' => $errorMessage];
    }

    /**
     * Send Quotation to customer via email with PDF document attached.
     */
    public function sendEmail(Request $request, Quotation $quotation): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'email'          => ['required', 'email'],
            'subject'        => ['nullable', 'string', 'max:255'],
            'custom_message' => ['nullable', 'string', 'max:2000'],
            'pdf_format'     => ['nullable', 'in:1,2,3,4'],
            'pricing_mode'   => ['nullable', 'in:itemized,total_only'],
            'smtp_username'  => ['nullable', 'string', 'max:255'],
            'smtp_password'  => ['nullable', 'string', 'max:255'],
            'smtp_host'      => ['nullable', 'string', 'max:255'],
            'smtp_port'      => ['nullable', 'integer'],
            'smtp_encryption'=> ['nullable', 'string', 'max:10'],
        ]);

        if ($quotation->status === 'pending_approval' && Auth::user()->isTechnician()) {
            return back()->with('error', 'This quotation is pending Admin pricing and product approval. Once approved, you will be able to share it with the customer.');
        }

        // Save on-the-fly SMTP credentials if supplied from the Quick Setup drawer
        if ($request->filled('smtp_username') && $request->filled('smtp_password')) {
            $settings = \App\Models\GatewaySetting::getSettings();
            $smtpUser = trim((string) $request->input('smtp_username'));
            $isGmail = str_contains(strtolower($smtpUser), '@gmail.com');
            $settings->update([
                'smtp_mailer'       => 'smtp',
                'smtp_host'         => $request->input('smtp_host') ?: ($isGmail ? 'smtp.gmail.com' : 'smtp.gmail.com'),
                'smtp_port'         => (int) ($request->input('smtp_port') ?: 587),
                'smtp_encryption'   => $request->input('smtp_encryption') ?: 'tls',
                'smtp_username'     => $smtpUser,
                'smtp_password'     => trim((string) $request->input('smtp_password')),
                'smtp_from_address' => $smtpUser,
                'smtp_from_name'    => $settings->smtp_from_name ?: config('app.name', 'Precision IT Systems'),
            ]);
            \App\Services\MailConfigService::applyDynamicMailConfig();
        }

        $recipientEmail = $validated['email'];
        $subject        = $validated['subject'] ?? null;
        $customMessage  = $validated['custom_message'] ?? null;
        $pdfFormat      = $validated['pdf_format'] ?? $quotation->pdf_format ?? '1';
        $pricingMode    = $validated['pricing_mode'] ?? $quotation->pricing_mode ?? 'itemized';

        $result = $this->dispatchQuotationEmail(
            $quotation,
            $recipientEmail,
            $subject,
            $customMessage,
            $pdfFormat,
            $pricingMode
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'     => $result['success'],
                'is_log_mode' => $result['is_log_mode'] ?? false,
                'message'     => $result['success']
                    ? "Official Quotation #{$quotation->quotation_no} with attached PDF has been successfully dispatched to {$recipientEmail}!"
                    : ($result['error'] ?? 'SMTP delivery error'),
                'error'       => $result['error'],
            ]);
        }

        if (!$result['success']) {
            return back()->with('error', "Failed to send email to {$recipientEmail}: {$result['error']}. Please check your SMTP mail settings in .env.");
        }

        $mailer = config('mail.default', 'log');
        $note = $mailer === 'log' ? ' (Note: MAIL_MAILER=log in .env, email was written to log file. Configure SMTP in .env to deliver real emails to inbox).' : '';

        return back()->with('status', "Official Quotation #{$quotation->quotation_no} with attached PDF has been successfully emailed to {$recipientEmail}!{$note}");
    }

    /**
     * Admin & Office review and approval of field quotation pricing & product details.
     */
    public function approve(Request $request, Quotation $quotation): RedirectResponse
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isOffice()) {
            abort(403, 'Unauthorized action.');
        }

        if (!in_array($quotation->status, ['pending_approval', 'draft'])) {
            return back()->with('status', 'Quotation is already approved or processed.');
        }

        $oldStatus = $quotation->status;
        $chosenFormat = $request->input('pdf_format', $quotation->pdf_format ?? '1');
        if (!in_array($chosenFormat, ['1', '2', '3', '4'])) {
            $chosenFormat = '1';
        }
        $chosenPricingMode = $request->input('pricing_mode', $quotation->pricing_mode ?? 'itemized');
        if (!in_array($chosenPricingMode, ['itemized', 'total_only'])) {
            $chosenPricingMode = 'itemized';
        }

        DB::transaction(function () use ($quotation, $oldStatus, $chosenFormat, $chosenPricingMode) {
            $quotation->update([
                'status'       => 'approved',
                'pdf_format'   => $chosenFormat,
                'pricing_mode' => $chosenPricingMode,
                'approved_by'  => Auth::id(),
                'approved_at'  => now(),
            ]);

            $formatName = Quotation::FORMATS[$chosenFormat] ?? 'Format ' . $chosenFormat;
            $modeName = Quotation::PRICING_MODES[$chosenPricingMode] ?? 'With Product Cost & Total';

            $quotation->statusHistories()->create([
                'changed_by' => Auth::id(),
                'from_status' => $oldStatus,
                'to_status' => 'approved',
                'notes' => 'Pricing, scope, ' . $formatName . ' format (' . $modeName . ') approved by ' . Auth::user()->name . '. Ready for client dispatch.',
            ]);

            // Notify technician that their quotation has been approved
            if ($quotation->created_by && $quotation->creator) {
                try {
                    \App\Models\NotificationLog::create([
                        'channel' => 'email',
                        'event_type' => 'quotation_approved',
                        'recipient_type' => 'technician',
                        'recipient_name' => $quotation->creator->name,
                        'recipient_email' => $quotation->creator->email,
                        'recipient_phone' => $quotation->creator->phone,
                        'subject' => "Quotation #{$quotation->quotation_no} Approved",
                        'message_body' => "Admin " . Auth::user()->name . " approved quotation #{$quotation->quotation_no} for " . ($quotation->lead?->customer_name ?? 'Client') . ". You can now share it via WhatsApp & Email.",
                        'action_url' => route('quotations.show', $quotation),
                        'status' => 'delivered',
                        'reference_type' => get_class($quotation),
                        'reference_id' => $quotation->id,
                        'sent_at' => now(),
                        'created_by' => Auth::id(),
                    ]);
                } catch (\Throwable $e) {}
            }
        });

        \Illuminate\Support\Facades\Cache::flush();

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('status', "Quotation #{$quotation->quotation_no} approved successfully! The technician can now share it with the customer.");
    }

    public function markAccepted(Quotation $quotation): RedirectResponse
    {
        if (!in_array($quotation->status, ['draft', 'approved', 'sent'])) {
            return back()->with(
                'status',
                'Only draft, approved, or sent quotations can be accepted.'
            );
        }

        $oldStatus = $quotation->status;

        DB::transaction(function () use ($quotation, $oldStatus) {
            $quotation->update([
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);

            $quotation->lead->update([
                'status' => 'won',
            ]);

            $quotation->statusHistories()->create([
                'changed_by' => Auth::id(),
                'from_status' => $oldStatus,
                'to_status' => 'accepted',
                'notes' => 'Customer accepted the quotation.',
            ]);
        });

        $quotation->load(['items.product', 'lead']);

        // Check whether any quoted product has zero stock or insufficient stock (shortage)
        $hasShortage = false;
        foreach ($quotation->items as $item) {
            $product = $item->product;
            if ($product) {
                $stock = (float) $product->stock_quantity;
                $required = (float) $item->quantity;
                if ($stock <= 0 || $stock < $required) {
                    $hasShortage = true;
                    break;
                }
            }
        }

        if ($hasShortage) {
            return redirect()
                ->route('quotations.procurement-check', $quotation)
                ->with('status', '✓ Quotation accepted! Warehouse stock analysis detected zero or low stock for quoted items. Review deficit and purchase orders below.');
        }

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('status', 'Quotation marked as accepted successfully. All quoted products are available in warehouse stock.');
    }

    /**
     * Dedicated procurement shortage review page: displays ONLY low-stock and zero-stock
     * products from this accepted quotation for analysis by Admins and Employees.
     */
    public function procurementCheck(Quotation $quotation): View|RedirectResponse
    {
        if (!Auth::user()->isOffice()) {
            abort(403, 'Unauthorized. Only Office management and staff can view procurement deficit analysis.');
        }

        $quotation->load(['items.product', 'lead']);

        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();

        $shortageItems = [];
        $totalEstimatedCost = 0.0;
        $totalUnitsToOrder = 0;

        foreach ($quotation->items as $item) {
            $product = $item->product;
            $stock = $product ? (float) $product->stock_quantity : 0.0;
            $required = (float) $item->quantity;

            $isZeroStock = ($stock <= 0);
            $isLowStock = ($stock > 0 && $stock < $required);

            // ONLY include products that have zero stock or low stock (insufficient to fulfill the quote)
            if ($isZeroStock || $isLowStock) {
                $deficit = max(0, $required - max(0, $stock));
                if ($deficit <= 0) {
                    $deficit = $required;
                }

                $unitCost = (float) ($product?->cost_price ?: ($product?->unit_price ?: (float) $item->unit_price));
                $lineCost = round($deficit * $unitCost, 2);
                $totalEstimatedCost += $lineCost;
                $totalUnitsToOrder += $deficit;

                // Match suggested supplier based on product category if available
                $suggestedSupplierId = $suppliers->first()?->id;
                if ($product && $product->category) {
                    $matchingSupplier = $suppliers->first(function ($s) use ($product) {
                        return is_array($s->categories)
                            ? in_array($product->category, $s->categories)
                            : str_contains((string) $s->categories, (string) $product->category);
                    });
                    if ($matchingSupplier) {
                        $suggestedSupplierId = $matchingSupplier->id;
                    }
                }

                $shortageItems[] = [
                    'item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'item_name' => $item->item_name,
                    'sku' => $product?->sku ?? 'CUSTOM',
                    'category' => $product?->category ?? 'General Hardware',
                    'unit' => $item->unit ?: 'Nos',
                    'quoted_quantity' => $required,
                    'stock_on_hand' => $stock,
                    'is_zero_stock' => $isZeroStock,
                    'is_low_stock' => $isLowStock,
                    'order_quantity' => $deficit,
                    'unit_cost' => $unitCost,
                    'tax_rate' => (float) ($product?->tax_rate ?: 18.0),
                    'total_cost' => $lineCost,
                    'suggested_supplier_id' => $suggestedSupplierId,
                ];
            }
        }

        return view('quotations.procurement_shortage', compact(
            'quotation',
            'shortageItems',
            'suppliers',
            'totalEstimatedCost',
            'totalUnitsToOrder'
        ));
    }

    /**
     * Generate Purchase Order(s) from analyzed quotation shortage items. Admin only.
     */
    public function generatePoFromShortage(Request $request, Quotation $quotation): RedirectResponse
    {
        // Only Admin can view and purchase the orders
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action. Only Administrators can generate and execute purchase orders.');
        }

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:quotation_items,id'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.supplier_id' => ['required', 'exists:suppliers,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
            'expected_delivery_date' => ['nullable', 'date'],
        ]);

        $settings = GatewaySetting::first();
        $companyStateCode = $settings?->company_state_code ?: '33';
        $seqService = app(DocumentSequenceService::class);

        // Group by supplier
        $grouped = [];
        foreach ($validated['items'] as $itemData) {
            $grouped[$itemData['supplier_id']][] = $itemData;
        }

        $createdPOs = [];

        DB::transaction(function () use ($grouped, $quotation, $validated, $companyStateCode, $seqService, &$createdPOs) {
            foreach ($grouped as $supplierId => $items) {
                $supplier = Supplier::findOrFail($supplierId);
                $vendorStateCode = $supplier->gst_number ? GstHelper::extractStateCode($supplier->gst_number) : null;
                $poNumber = $seqService->nextPurchaseOrderNumber();

                $subtotal = 0.0;
                $cgstTotal = 0.0;
                $sgstTotal = 0.0;
                $igstTotal = 0.0;

                foreach ($items as $it) {
                    $qty = (int) $it['quantity'];
                    $cost = (float) $it['unit_cost'];
                    $taxRate = (float) ($it['tax_rate'] ?? 18.0);
                    $lineSubtotal = round($qty * $cost, 2);
                    $subtotal += $lineSubtotal;

                    $taxSplit = GstHelper::calculateTaxSplit($lineSubtotal, $taxRate, $vendorStateCode, $companyStateCode);
                    $cgstTotal += $taxSplit['cgst_amount'];
                    $sgstTotal += $taxSplit['sgst_amount'];
                    $igstTotal += $taxSplit['igst_amount'];
                }

                $taxAmount = round($cgstTotal + $sgstTotal + $igstTotal, 2);
                $grandTotal = round($subtotal + $taxAmount, 2);

                $po = PurchaseOrder::create([
                    'supplier_id' => $supplierId,
                    'project_id' => null,
                    'po_number' => $poNumber,
                    'order_date' => now()->toDateString(),
                    'expected_delivery_date' => $validated['expected_delivery_date'] ?? now()->addDays(5)->toDateString(),
                    'status' => PurchaseOrder::STATUS_DRAFT,
                    'subtotal' => $subtotal,
                    'tax_percent' => 18.00,
                    'tax_amount' => $taxAmount,
                    'cgst_amount' => $cgstTotal,
                    'sgst_amount' => $sgstTotal,
                    'igst_amount' => $igstTotal,
                    'total' => $grandTotal,
                    'payment_status' => 'unpaid',
                    'amount_paid' => 0.00,
                    'notes' => "Quotation #{$quotation->quotation_no} Shortage Order for {$quotation->lead->customer_name}. " . ($validated['notes'] ?? ''),
                    'created_by' => Auth::id(),
                ]);

                foreach ($items as $it) {
                    $qItem = QuotationItem::with('product')->findOrFail($it['item_id']);
                    $qty = (int) $it['quantity'];
                    $cost = (float) $it['unit_cost'];
                    $taxRate = (float) ($it['tax_rate'] ?? 18.0);
                    $lineSubtotal = round($qty * $cost, 2);
                    $taxSplit = GstHelper::calculateTaxSplit($lineSubtotal, $taxRate, $vendorStateCode, $companyStateCode);

                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'product_id' => $it['product_id'] ?? $qItem->product_id,
                        'item_name' => $qItem->item_name,
                        'sku' => $qItem->product?->sku ?? 'CUSTOM',
                        'unit_cost' => $cost,
                        'quantity_ordered' => $qty,
                        'quantity_received' => 0,
                        'total_cost' => $lineSubtotal,
                        'tax_rate' => $taxRate,
                        'cgst_rate' => $taxSplit['cgst_rate'],
                        'cgst_amount' => $taxSplit['cgst_amount'],
                        'sgst_rate' => $taxSplit['sgst_rate'],
                        'sgst_amount' => $taxSplit['sgst_amount'],
                        'igst_rate' => $taxSplit['igst_rate'],
                        'igst_amount' => $taxSplit['igst_amount'],
                    ]);
                }

                $createdPOs[] = $po;
            }
        });

        $firstPo = $createdPOs[0] ?? null;
        if ($firstPo) {
            return redirect()
                ->route('purchase-orders.show', $firstPo)
                ->with('status', "✓ Purchase Order {$firstPo->po_number} generated for quoted stock shortage! You can now analyze and download the PO PDF immediately for purchasing.")
                ->with('show_pdf_ready', true);
        }

        return redirect()->route('purchase-orders.index')->with('status', 'Purchase order created.');
    }

    public function markRejected(Request $request, Quotation $quotation): RedirectResponse 
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        if (!in_array($quotation->status, ['draft', 'sent'])) {
            return back()->with(
                'status',
                'Only draft or sent quotations can be rejected.'
            );
        }

        $oldStatus = $quotation->status;

        DB::transaction(function () use ($quotation, $data, $oldStatus) {
            $quotation->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejection_reason' => $data['rejection_reason'],
            ]);

            $quotation->statusHistories()->create([
                'changed_by' => Auth::id(),
                'from_status' => $oldStatus,
                'to_status' => 'rejected',
                'notes' => $data['rejection_reason'],
            ]);

            $hasActiveQuotation = Quotation::query()
                ->where('lead_id', $quotation->lead_id)
                ->whereIn('status', [
                    'draft',
                    'sent',
                    'accepted',
                ])
                ->exists();

            if (! $hasActiveQuotation) {
                $quotation->lead->update([
                    'status' => 'lost',
                ]);
            }
        });

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('status', 'Quotation marked as rejected successfully.');
    }

   public function markSent(Quotation $quotation): RedirectResponse
   {
    if ($quotation->status !== 'draft') {
        return back()->with(
            'status',
            'Only draft quotations can be marked as sent.'
        );
    }

    DB::transaction(function () use ($quotation) {
        $quotation->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $quotation->statusHistories()->create([
            'changed_by' => Auth::id(),
            'from_status' => 'draft',
            'to_status' => 'sent',
            'notes' => 'Quotation marked as sent to customer.',
        ]);
    });

    return redirect()
        ->route('quotations.show', $quotation)
        ->with('status', 'Quotation marked as sent successfully.');
}

    public function edit(Quotation $quotation): View|RedirectResponse
    {
        if (!in_array($quotation->status, ['draft', 'pending_approval', 'approved', 'sent'])) {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('status', 'This quotation cannot be edited.');
        }

        $quotation->load([
            'items.product',
            'lead',
        ]);

        $suppliers = \App\Models\Supplier::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'company_name', 'phone', 'categories', 'brands_supplied']);

        return view('quotations.edit', compact('quotation', 'suppliers'));
    }

    public function update(
        StoreQuotationRequest $request,
        Quotation $quotation
    ): RedirectResponse {
        if (!in_array($quotation->status, ['draft', 'pending_approval', 'approved', 'sent'])) {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('status', 'This quotation cannot be edited.');
        }

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $quotation) {
            $format = $validated['pdf_format'] ?? $quotation->pdf_format ?? '1';

            $quotation->update([
                'quotation_date' => $validated['quotation_date'],
                'valid_until' => $validated['valid_until'] ?? null,
                'tax_percent' => (float) ($validated['tax_percent'] ?? 0),
                'discount' => (float) ($validated['discount'] ?? 0),
                'notes' => $validated['notes'] ?? null,
                'pdf_format' => $format,
            ]);

            $quotation->items()->delete();

            foreach ($validated['items'] as $item) {
                $price = isset($item['unit_price']) && is_numeric($item['unit_price']) ? (float) $item['unit_price'] : 0;
                $catalogProduct = null;
                if (!empty($item['product_id'])) {
                    $catalogProduct = \App\Models\Product::find($item['product_id']);
                }
                if (!$catalogProduct && !empty($item['item_name'])) {
                    $catalogProduct = \App\Models\Product::where('name', trim($item['item_name']))
                        ->orWhere('sku', trim($item['item_name']))
                        ->first();
                    if ($catalogProduct) {
                        $item['product_id'] = $catalogProduct->id;
                    }
                }

                if ($price <= 0 && $catalogProduct && (float) $catalogProduct->unit_price > 0) {
                    $price = (float) $catalogProduct->unit_price;
                }
                $lineTotal = (float) $item['quantity'] * $price;

                $quotation->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => (float) $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_price' => $price,
                    'total' => $lineTotal,
                ]);
            }

            $quotation->recalculateTotals();

            $formatName = Quotation::FORMATS[$format] ?? 'Format ' . $format;
            $quotation->statusHistories()->create([
                'changed_by' => Auth::id(),
                'from_status' => $quotation->status,
                'to_status' => $quotation->status,
                'notes' => Auth::user()->name . " updated quotation items, pricing & format ({$formatName}). Grand Total: " . \App\Support\Inr::money($quotation->total),
            ]);
        });

        // Flush notification cache for creator
        if ($quotation->created_by) {
            \App\Services\HeaderNotificationService::clearCacheForUser($quotation->created_by);
        }

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('status', 'Quotation items, pricing, and format updated successfully.');
    }

    /**
     * Direct endpoint to quickly update item pricing and quantities from show view.
     */
    public function updatePricing(Request $request, Quotation $quotation): RedirectResponse
    {
        if (!Auth::user()->isOffice()) {
            abort(403, 'Unauthorized action.');
        }

        if (!in_array($quotation->status, ['draft', 'pending_approval', 'approved', 'sent'])) {
            return back()->with('error', 'Cannot update pricing on this quotation in its current status.');
        }

        $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['nullable', 'integer', 'exists:quotation_items,id'],
            'items.*.item_name' => ['nullable', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.unit' => ['nullable', 'string', 'max:30'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'pdf_format' => ['nullable', 'string', 'in:1,2,3,4'],
        ]);

        DB::transaction(function () use ($request, $quotation) {
            foreach ($request->input('items') as $itemData) {
                $qty = (float) $itemData['quantity'];
                $price = (float) $itemData['unit_price'];

                if (!empty($itemData['id'])) {
                    $qItem = $quotation->items()->find($itemData['id']);
                    if ($qItem) {
                        $qItem->update([
                            'quantity' => $qty,
                            'unit_price' => $price,
                            'total' => $qty * $price,
                        ]);
                    }
                } elseif (!empty($itemData['item_name'])) {
                    $quotation->items()->create([
                        'product_id' => $itemData['product_id'] ?? null,
                        'item_name' => trim($itemData['item_name']),
                        'description' => $itemData['description'] ?? null,
                        'quantity' => $qty,
                        'unit' => $itemData['unit'] ?? 'Nos',
                        'unit_price' => $price,
                        'total' => $qty * $price,
                    ]);
                }
            }

            $updates = [];
            if ($request->has('tax_percent')) {
                $updates['tax_percent'] = (float) $request->input('tax_percent');
            }
            if ($request->has('discount')) {
                $updates['discount'] = (float) $request->input('discount');
            }
            if ($request->filled('pdf_format')) {
                $updates['pdf_format'] = $request->input('pdf_format');
            }
            if (!empty($updates)) {
                $quotation->update($updates);
            }

            $quotation->recalculateTotals();

            $quotation->statusHistories()->create([
                'changed_by' => Auth::id(),
                'from_status' => $quotation->status,
                'to_status' => $quotation->status,
                'notes' => Auth::user()->name . ' updated item prices & quantities. Grand Total: ' . \App\Support\Inr::money($quotation->total),
            ]);
        });

        // Flush caches
        if ($quotation->created_by) {
            \App\Services\HeaderNotificationService::clearCacheForUser($quotation->created_by);
        }

        return back()->with('status', 'Quotation pricing and product items updated successfully! All changes are now live.');
    }

    /**
     * Dedicated endpoint for both Admin and Technician to set/update Quotation Format.
     */
    public function updateFormat(Request $request, Quotation $quotation): RedirectResponse
    {
        $validated = $request->validate([
            'pdf_format'   => ['nullable', 'string', 'in:1,2,3,4'],
            'pricing_mode' => ['nullable', 'string', 'in:itemized,total_only'],
        ]);

        $newFormat = $validated['pdf_format'] ?? $quotation->pdf_format ?? '1';
        $newPricingMode = $validated['pricing_mode'] ?? $quotation->pricing_mode ?? 'itemized';

        $quotation->update([
            'pdf_format'   => $newFormat,
            'pricing_mode' => $newPricingMode,
        ]);

        $formatName = Quotation::FORMATS[$newFormat] ?? 'Format ' . $newFormat;
        $modeName = Quotation::PRICING_MODES[$newPricingMode] ?? 'With Product Cost & Total';
        $user = Auth::user();

        $quotation->statusHistories()->create([
            'changed_by' => $user->id,
            'from_status' => $quotation->status,
            'to_status' => $quotation->status,
            'notes' => "{$user->name} set quotation format to '{$formatName}' ({$modeName}).",
        ]);

        $message = $user->isTechnician()
            ? "Quotation layout set to '{$formatName}' with '{$modeName}'. Forwarded to Admin for review & approval."
            : "Quotation format updated to '{$formatName}' with '{$modeName}'.";

        return back()->with('status', $message);
    }

    public function destroy(Quotation $quotation): RedirectResponse
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        if (in_array($quotation->status, ['accepted'])) {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('status', 'Cannot delete an accepted quotation.');
        }

        $leadId = $quotation->lead_id;
        $quotation->delete();

        return redirect()
            ->route('quotations.index')
            ->with('status', 'Quotation deleted successfully.');
    }

    /**
     * Remove a single line item from a quotation (draft, pending_approval, or approved).
     */
    public function destroyItem(QuotationItem $item): RedirectResponse
    {
        $quotation = $item->quotation;

        if (!in_array($quotation->status, ['draft', 'pending_approval', 'approved', 'sent'])) {
            return back()->with('status', 'Cannot remove items from this quotation.');
        }

        $itemName = $item->item_name;
        $item->delete();
        $quotation->recalculateTotals();

        $quotation->statusHistories()->create([
            'changed_by' => Auth::id(),
            'from_status' => $quotation->status,
            'to_status' => $quotation->status,
            'notes' => Auth::user()->name . " deleted item '{$itemName}' from quotation.",
        ]);

        return back()->with('status', "Item '{$itemName}' removed successfully.");
    }

    /** Saved views shown in the view menu. */
    public const VIEWS = [
        'all'     => 'All Quotations',
        'pending' => 'Pending Review',
        'open'    => 'Open Deals',
        'won'     => 'Won Deals',
        'lost'    => 'Lost Deals',
    ];

    /** Quick filters in the filter panel's first section. */
    public const QUICK_FILTERS = [
        'recent'   => 'Created in last 7 days',
        'expiring' => 'Expiring in 7 days',
        'overdue'  => 'Past valid date, still open',
        'no_job'   => 'Accepted, no job yet',
        'has_job'  => 'Has an installation job',
    ];

    public const SORTS = [
        'date_desc'   => 'Newest first',
        'date_asc'    => 'Oldest first',
        'total_desc'  => 'Total, high to low',
        'total_asc'   => 'Total, low to high',
        'valid_asc'   => 'Valid until, soonest',
        'customer_asc' => 'Customer, A to Z',
    ];

    public function index(Request $request)
    {
        $view = array_key_exists($request->get('view'), self::VIEWS) ? $request->get('view') : 'all';
        $sort = array_key_exists($request->get('sort'), self::SORTS) ? $request->get('sort') : 'date_desc';
        $layout = $request->get('layout') === 'board' ? 'board' : 'list';
        $perPage = in_array((int) $request->get('per_page'), [10, 20, 50, 100], true) ? (int) $request->get('per_page') : 20;

        $query = $this->filteredQuery($request);
        $quotations = (clone $query)->paginate($perPage)->withQueryString();

        // The board is the deals pipeline: everything that matches (up to 200) by status
        $board = $layout === 'board'
            ? (clone $query)->limit(200)->get()->groupBy('status')
            : collect();

        $statusCounts = Quotation::selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status');

        return view('quotations.index', compact('quotations', 'board', 'statusCounts', 'view', 'sort', 'layout', 'perPage'));
    }

    /** The current list as a CSV file, with the same filters and order as the screen. */
    public function export(Request $request)
    {
        $quotations = $this->filteredQuery($request)->get();

        return response()->streamDownload(function () use ($quotations) {
            $out = fopen('php://output', 'w');
            \App\Support\CsvHelper::writeRow($out, ['Quotation no', 'Customer', 'Phone', 'Quotation date', 'Valid until', 'Subtotal', 'Tax', 'Total', 'Status']);
            foreach ($quotations as $q) {
                \App\Support\CsvHelper::writeRow($out, [
                    $q->quotation_no, $q->lead?->customer_name, $q->lead?->phone, $q->quotation_date?->format('Y-m-d'),
                    $q->valid_until?->format('Y-m-d'), $q->subtotal, $q->tax_amount, $q->total, ucfirst($q->status),
                ]);
            }
            fclose($out);
        }, 'quotations-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * Ticked quotations: mark drafts as sent (with the same status history as the single
     * button), or, for admins, delete drafts. Other quotations in the selection are skipped.
     */
    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids'    => 'required|array|min:1|max:500',
            'ids.*'  => 'integer|exists:quotations,id',
            'action' => 'required|in:sent,delete',
        ]);

        $drafts = Quotation::whereIn('id', $data['ids'])->where('status', 'draft')->get();
        $skipped = count($data['ids']) - $drafts->count();
        $note = $skipped > 0 ? " {$skipped} skipped (only drafts can be " . ($data['action'] === 'sent' ? 'marked sent' : 'deleted') . ').' : '';

        if ($data['action'] === 'delete') {
            abort_unless($request->user()->isAdmin(), 403);
            $drafts->each->delete();
            return back()->with('status', $drafts->count() . ' ' . str('quotation')->plural($drafts->count()) . ' deleted.' . $note);
        }

        DB::transaction(function () use ($drafts) {
            foreach ($drafts as $quotation) {
                $quotation->update(['status' => 'sent', 'sent_at' => now()]);
                $quotation->statusHistories()->create([
                    'changed_by' => Auth::id(),
                    'from_status' => 'draft',
                    'to_status' => 'sent',
                    'notes' => 'Quotation marked as sent to customer.',
                ]);
            }
        });

        return back()->with('status', $drafts->count() . ' ' . str('quotation')->plural($drafts->count()) . ' marked sent.' . $note);
    }

    /** Search, view, quick filters, field filters, customer letter and sort, shared by list and export. */
    private function filteredQuery(Request $request)
    {
        $query = Quotation::with('lead');
        $like = $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $today = now()->toDateString();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search, $like) {
                $q->where('quotation_no', $like, "%{$search}%")
                  ->orWhereHas('lead', function ($l) use ($search, $like) {
                      $l->where('customer_name', $like, "%{$search}%")
                        ->orWhere('phone', $like, "%{$search}%")
                        ->orWhere('email', $like, "%{$search}%")
                        ->orWhere('site_address', $like, "%{$search}%");
                  });
            });
        }

        match ($request->get('view')) {
            'pending' => $query->where('status', 'pending_approval'),
            'open'    => $query->whereIn('status', ['draft', 'approved', 'sent']),
            'won'     => $query->where('status', 'accepted'),
            'lost'    => $query->whereIn('status', ['rejected', 'expired']),
            default   => null,
        };

        foreach ((array) $request->get('only', []) as $quick) {
            match ($quick) {
                'recent'   => $query->where('created_at', '>=', now()->subDays(7)),
                'expiring' => $query->whereIn('status', ['draft', 'sent'])->whereBetween('valid_until', [$today, now()->addDays(7)->toDateString()]),
                'overdue'  => $query->whereIn('status', ['draft', 'sent'])->whereDate('valid_until', '<', $today),
                'no_job'   => $query->where('status', 'accepted')->doesntHave('installationJob'),
                'has_job'  => $query->has('installationJob'),
                default    => null,
            };
        }

        if ($request->filled('status')) {
            $query->whereIn('status', (array) $request->status);
        }
        if (is_numeric($request->get('min_total'))) {
            $query->where('total', '>=', (float) $request->get('min_total'));
        }
        if (is_numeric($request->get('max_total'))) {
            $query->where('total', '<=', (float) $request->get('max_total'));
        }
        if (preg_match('/^[A-Z]$/', (string) $request->get('letter'))) {
            $query->whereHas('lead', fn ($l) => $l->where('customer_name', $like, $request->get('letter') . '%'));
        }

        match ($request->get('sort')) {
            'date_asc'     => $query->orderBy('created_at'),
            'total_desc'   => $query->orderByDesc('total'),
            'total_asc'    => $query->orderBy('total'),
            'valid_asc'    => $query->orderByRaw('valid_until is null')->orderBy('valid_until'),
            'customer_asc' => $query->orderBy(Lead::select('customer_name')->whereColumn('leads.id', 'quotations.lead_id')),
            default        => $query->orderByDesc('created_at'),
        };

        return $query->orderByDesc('id');
    }

    /**
     * Download customer contact as vCard (.vcf) directly from Quotation screen.
     */
    public function downloadVcard(Request $request, Quotation $quotation, \App\Services\VcardExportService $vcardService)
    {
        $lead = $quotation->lead;
        if (!$lead) {
            abort(404, 'Associated customer lead not found.');
        }

        $overridePhone = $request->query('phone') ?: $quotation->getWhatsAppPhone();
        $overrideName  = $request->query('name');
        $disposition   = $request->query('mode', 'attachment');

        return $vcardService->downloadResponse($lead, $overridePhone, $overrideName, $disposition);
    }
}