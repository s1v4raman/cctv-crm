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
use Illuminate\View\View;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotationController extends Controller
{
    public function create(Lead $lead): View
    {
        return view('quotations.create', compact('lead'));
    }

    public function createGeneral(): View
    {
        $leads = Lead::orderBy('customer_name')->get();
        return view('quotations.select_lead', compact('leads'));
    }

    public function store(StoreQuotationRequest $request, Lead $lead): RedirectResponse
    {
        $data = $request->validated();

        $quotation = DB::transaction(function () use ($data, $lead) {
            $subtotal = 0;

            foreach ($data['items'] as $item) {
                $subtotal += (float) $item['quantity'] * (float) $item['unit_price'];
            }

            $requestedDiscount = (float) ($data['discount'] ?? 0);
            $discount = min($requestedDiscount, $subtotal);

            $taxPercent = (float) ($data['tax_percent'] ?? 0);
            $taxableAmount = $subtotal - $discount;
            $taxAmount = $taxableAmount * ($taxPercent / 100);
            $grandTotal = $taxableAmount + $taxAmount;

            $quotation = Quotation::create([
                'lead_id' => $lead->id,
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
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $lineTotal = (float) $item['quantity'] * (float) $item['unit_price'];

                $quotation->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => (float) $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_price' => (float) $item['unit_price'],
                    'total' => $lineTotal,
                ]);
            }

            $lead->update([
                'status' => 'quoted',
            ]);

            return $quotation;
        });

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('status', 'Quotation created successfully.');
    }

    public function show(Quotation $quotation): View
    {
        $quotation->load([
            'lead',
            'items',
            'installationJob',
            'statusHistories.changedBy',
        ]);

        return view('quotations.show', compact('quotation'));
    }

    public function downloadPdf(Quotation $quotation, string $format)
    {
        $quotation->load(['lead', 'items']);

        if (!in_array($format, ['1', '2', '3'])) {
            $format = '1';
        }

        $pdf = Pdf::loadView('quotations.pdf.format' . $format, compact('quotation'));

        return $pdf->download($quotation->quotation_no . '_format_' . $format . '.pdf');
    }

    public function publicPdf(Quotation $quotation, ?string $format = '1')
    {
        $quotation->load(['lead', 'items']);

        if (!in_array($format, ['1', '2', '3'])) {
            $format = '1';
        }

        $pdf = Pdf::loadView('quotations.pdf.format' . $format, compact('quotation'));

        return $pdf->stream($quotation->quotation_no . '_format_' . $format . '.pdf');
    }

    /**
     * Dispatch and automatically open WhatsApp with the customer's phone number and quotation summary.
     */
    public function sendWhatsApp(Request $request, Quotation $quotation)
    {
        $quotation->load(['lead', 'items.product']);

        $format = $request->input('format', '1');
        if (!in_array($format, ['1', '2', '3'])) {
            $format = '1';
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
        }

        $customMsg = $request->input('custom_message');
        $phone = $quotation->getWhatsAppPhone($customPhone);
        $message = $customMsg ?: $quotation->getWhatsAppFormattedMessage($format);

        if ($quotation->status === 'draft') {
            $quotation->update([
                'status'  => 'sent',
                'sent_at' => now(),
            ]);

            $quotation->statusHistories()->create([
                'changed_by'  => Auth::id(),
                'from_status' => 'draft',
                'to_status'   => 'sent',
                'notes'       => "Quotation PDF (Format {$format}) shared with customer via WhatsApp (" . ($phone ?: 'No Phone') . ").",
            ]);
        } else {
            $quotation->statusHistories()->create([
                'changed_by'  => Auth::id(),
                'from_status' => $quotation->status,
                'to_status'   => $quotation->status,
                'notes'       => "Quotation PDF (Format {$format}) re-shared with customer via WhatsApp (" . ($phone ?: 'No Phone') . ").",
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
                'action_url'      => route('quotations.public-pdf', ['quotation' => $quotation, 'format' => $format]),
                'status'          => 'sent',
                'reference_type'  => get_class($quotation),
                'reference_id'    => $quotation->id,
                'sent_at'         => now(),
                'created_by'      => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Quotation WhatsApp NotificationLog write failed: " . $e->getMessage());
        }

        $whatsappUrl = $quotation->getWhatsAppUrl($format, $customPhone, $customMsg);
        $webUrl = $quotation->getWhatsAppWebUrl($format, $customPhone, $customMsg);
        $appUrl = $quotation->getWhatsAppAppUrl($format, $customPhone, $customMsg);
        $smsUrl = $quotation->getSmsUrl($format, $customPhone, $customMsg);
        $pdfUrl = route('quotations.pdf', ['quotation' => $quotation, 'format' => $format]);
        $vcardUrl = route('quotations.vcard', $quotation);

        if ($request->expectsJson() || $request->ajax()) {
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
            ]);
        }

        return redirect()->away($whatsappUrl);
    }

    /**
     * Send Quotation to customer via email with PDF document attached.
     */
    public function sendEmail(Request $request, Quotation $quotation): RedirectResponse
    {
        $validated = $request->validate([
            'email'          => ['required', 'email'],
            'subject'        => ['nullable', 'string', 'max:255'],
            'custom_message' => ['nullable', 'string', 'max:2000'],
            'pdf_format'     => ['nullable', 'in:1,2,3'],
        ]);

        $recipientEmail = $validated['email'];
        $subject        = $validated['subject'] ?? null;
        $customMessage  = $validated['custom_message'] ?? null;
        $pdfFormat      = $validated['pdf_format'] ?? '1';

        // 1. Synchronize & persist customer email to Lead record if empty or updated
        if ($quotation->lead && ($quotation->lead->email !== $recipientEmail)) {
            $quotation->lead->update(['email' => $recipientEmail]);
        }

        // 2. Apply Dynamic SMTP Configuration & Dispatch Email with Attached PDF
        \App\Services\MailConfigService::applyDynamicMailConfig();

        $emailStatus = 'sent';
        $errorMessage = null;

        try {
            \Illuminate\Support\Facades\Mail::to($recipientEmail)
                ->send(new \App\Mail\QuotationEmailWithPdf($quotation, $customMessage, $subject, $pdfFormat));
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
            'action_url'      => route('quotations.public-pdf', ['quotation' => $quotation, 'format' => $pdfFormat]),
            'status'          => $emailStatus,
            'error_message'   => $errorMessage,
            'reference_type'  => get_class($quotation),
            'reference_id'    => $quotation->id,
            'sent_at'         => now(),
            'created_by'      => Auth::id(),
        ]);

        if ($emailStatus === 'failed') {
            return back()->with('error', "Failed to send email to {$recipientEmail}: {$errorMessage}. Please check your SMTP mail settings in .env.");
        }

        // 4. Update status to 'sent' if currently 'draft'
        if ($quotation->status === 'draft') {
            $quotation->update([
                'status'  => 'sent',
                'sent_at' => now(),
            ]);

            $quotation->statusHistories()->create([
                'changed_by'  => Auth::id(),
                'from_status' => 'draft',
                'to_status'   => 'sent',
                'notes'       => "Quotation PDF emailed to customer ({$recipientEmail}).",
            ]);
        } else {
            $quotation->statusHistories()->create([
                'changed_by'  => Auth::id(),
                'from_status' => $quotation->status,
                'to_status'   => $quotation->status,
                'notes'       => "Quotation PDF re-emailed to customer ({$recipientEmail}).",
            ]);
        }

        $mailer = config('mail.default', 'log');
        $note = $mailer === 'log' ? ' (Note: MAIL_MAILER=log in .env, email was written to log file. Configure SMTP in .env to deliver real emails to inbox).' : '';

        return back()->with('status', "Quotation #{$quotation->quotation_no} with attached PDF has been successfully dispatched to {$recipientEmail}!{$note}");
    }

    public function markAccepted(Quotation $quotation): RedirectResponse
    {
        if (!in_array($quotation->status, ['draft', 'sent'])) {
            return back()->with(
                'status',
                'Only draft or sent quotations can be accepted.'
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
        if ($quotation->status !== 'draft') {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('status', 'Only draft quotations can be edited.');
        }

        $quotation->load([
            'items',
            'lead',
        ]);

        return view('quotations.edit', compact('quotation'));
    }

    public function update(
        StoreQuotationRequest $request,
        Quotation $quotation
    ): RedirectResponse {
        if ($quotation->status !== 'draft') {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('status', 'Only draft quotations can be edited.');
        }

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $quotation) {
            $quotation->update([
                'quotation_date' => $validated['quotation_date'],
                'valid_until' => $validated['valid_until'] ?? null,
                'tax_percent' => (float) ($validated['tax_percent'] ?? 0),
                'discount' => (float) ($validated['discount'] ?? 0),
                'notes' => $validated['notes'] ?? null,
            ]);

            $quotation->items()->delete();

            foreach ($validated['items'] as $item) {
                $lineTotal = (float) $item['quantity'] * (float) $item['unit_price'];

                $quotation->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => (float) $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_price' => (float) $item['unit_price'],
                    'total' => $lineTotal,
                ]);
            }

            $quotation->recalculateTotals();
        });

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('status', 'Quotation updated successfully.');
    }

    public function destroy(Quotation $quotation): RedirectResponse
    {
        if ($quotation->status !== 'draft') {
            return redirect()
                ->route('quotations.show', $quotation)
                ->with('status', 'Only draft quotations can be deleted. Change status back to Draft first.');
        }

        $leadId = $quotation->lead_id;
        $quotation->delete();

        return redirect()
            ->route('quotations.index')
            ->with('status', 'Quotation deleted successfully.');
    }

    /**
     * Remove a single line item from a draft quotation.
     */
    public function destroyItem(QuotationItem $item): RedirectResponse
    {
        $quotation = $item->quotation;

        if ($quotation->status !== 'draft') {
            return back()->with('status', 'Cannot remove items from a non-draft quotation.');
        }

        $item->delete();
        $quotation->recalculateTotals();

        return back()->with('status', 'Item removed.');
    }

    public function index(Request $request)
    {
        $query = Quotation::with('lead');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('quotation_no', 'like', "%{$search}%")
                  ->orWhereHas('lead', function($l) use ($search) {
                      $l->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('site_address', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $quotations = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        return view('quotations.index', compact('quotations'));
    }

    /**
     * Download customer contact as vCard (.vcf) directly from Quotation screen.
     */
    public function downloadVcard(Quotation $quotation, \App\Services\VcardExportService $vcardService)
    {
        $lead = $quotation->lead;
        if (!$lead) {
            abort(404, 'Associated customer lead not found.');
        }

        return $vcardService->downloadResponse($lead);
    }
}