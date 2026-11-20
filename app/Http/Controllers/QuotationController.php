<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuotationRequest;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\QuotationItem;
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

            // Fetch catalog product prices if technician submitted items without price
            $processedItems = [];
            foreach ($data['items'] as $item) {
                $qty = (float) $item['quantity'];
                $price = isset($item['unit_price']) && is_numeric($item['unit_price']) ? (float) $item['unit_price'] : 0;
                
                if ($price <= 0 && !empty($item['product_id'])) {
                    $catalogProduct = \App\Models\Product::find($item['product_id']);
                    if ($catalogProduct && $catalogProduct->unit_price) {
                        $price = (float) $catalogProduct->unit_price;
                    }
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
                    ? 'Field technician ' . Auth::user()->name . ' created quotation with ' . count($processedItems) . ' items. Awaiting Admin pricing & product review.'
                    : 'Quotation created.',
            ]);

            return $quotation;
        });

        // Notify Admin & Employee teams immediately if technician submitted scope
        if ($isTech) {
            \App\Services\HeaderNotificationService::notifyAdminAndStaffOfQuotation($quotation, Auth::user());

            return redirect()
                ->route('technician.dashboard')
                ->with('status', "Quotation #{$quotation->quotation_no} submitted successfully! It has been forwarded to Admin & Employee teams for pricing and product review. Once approved, you can share it directly with the client.");
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
        ]);

        if ($quotation->status === 'pending_approval' && Auth::user()->isTechnician()) {
            return back()->with('error', 'This quotation is pending Admin pricing and product approval. Once approved, you will be able to share it with the customer.');
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

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('status', 'Quotation marked as accepted successfully.');
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
            'items',
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
                $lineTotal = (float) $item['quantity'] * (float) ($item['unit_price'] ?? 0);

                $quotation->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => (float) $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_price' => (float) ($item['unit_price'] ?? 0),
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