<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    use \App\Http\Controllers\Concerns\ListsRecords;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Supplier::class);

        $search = trim((string) $request->input('search', ''));
        $cityFilter = trim((string) $request->input('city', ''));
        $paymentTermsFilter = trim((string) $request->input('payment_terms', ''));
        $activeFilter = $request->input('active');

        $query = Supplier::query()->withCount('purchaseOrders');

        if (in_array($activeFilter, ['yes', 'no'], true)) {
            $query->where('is_active', $activeFilter === 'yes');
        }

        if ($cityFilter !== '' && $cityFilter !== 'all') {
            $query->where(function ($q) use ($cityFilter) {
                $q->where('city', 'like', "%{$cityFilter}%")
                    ->orWhere('state', 'like', "%{$cityFilter}%");
            });
        }

        if ($paymentTermsFilter !== '' && $paymentTermsFilter !== 'all') {
            $query->where('payment_terms', $paymentTermsFilter);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('state', 'like', "%{$search}%")
                    ->orWhere('brands_supplied', 'like', "%{$search}%")
                    ->orWhere('gst_number', 'like', "%{$search}%");
            });
        }

        $this->applyListSort($query, $request, [
            'date_desc'   => ['created_at', 'desc'],
            'name'        => ['name', 'asc'],
            'orders_desc' => ['purchase_orders_count', 'desc'],
        ]);
        $suppliers = $query->paginate($this->listPerPage($request))->withQueryString();

        $totalSuppliers = Supplier::count();
        $activeSuppliers = Supplier::where('is_active', true)->count();

        // Unique dynamic cities and payment terms for downward-opening dropdown filters
        $cities = Supplier::whereNotNull('city')->where('city', '!=', '')->distinct()->orderBy('city')->pluck('city')->all();
        $paymentTermsList = Supplier::whereNotNull('payment_terms')->where('payment_terms', '!=', '')->distinct()->orderBy('payment_terms')->pluck('payment_terms')->all();
        if (empty($paymentTermsList)) {
            $paymentTermsList = ['Immediate / Advance', 'Net 15 Days', 'Net 30 Days', 'Net 45 Days', 'Net 60 Days'];
        }

        return view('suppliers.index', compact('suppliers', 'totalSuppliers', 'activeSuppliers', 'cities', 'paymentTermsList'));
    }

    public function create(): View
    {
        $this->authorize('create', Supplier::class);
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', Supplier::class);
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        $supplier = Supplier::create($validated);

        // Added from the purchase order form's pop-up: hand back what the supplier list needs.
        if ($request->expectsJson()) {
            return response()->json([
                'id' => $supplier->id,
                'label' => $supplier->name . ($supplier->company_name ? " ({$supplier->company_name})" : ''),
                'message' => "Supplier '{$supplier->name}' added and selected.",
            ], 201);
        }

        return redirect()->route('suppliers.show', $supplier)->with('status', "Supplier '{$supplier->name}' registered successfully.");
    }

    public function show(Supplier $supplier): View
    {
        $this->authorize('view', $supplier);
        $supplier->load([
            'purchaseOrders.items',
            'purchaseOrders.createdBy',
            'vendorPayments.purchaseOrder',
            'vendorPayments.recordedBy',
        ]);
        return view('suppliers.show', compact('supplier'));
    }

    public function recordPayment(Request $request, Supplier $supplier): RedirectResponse
    {
        $this->authorize('recordPayment', $supplier);

        $validated = $request->validate([
            'purchase_order_id'     => ['nullable', 'exists:purchase_orders,id'],
            'amount'                => ['required', 'numeric', 'min:0.01'],
            'payment_date'          => ['required', 'date'],
            'payment_method'        => ['required', 'in:neft_rtgs,bank_transfer,cheque,upi,cash'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'notes'                 => ['nullable', 'string', 'max:1000'],
        ]);

        $amount = (float) $validated['amount'];

        if (!empty($validated['purchase_order_id'])) {
            $po = \App\Models\PurchaseOrder::where('supplier_id', $supplier->id)->find($validated['purchase_order_id']);
            if ($po && $amount > ($po->balanceDue() + 0.01)) {
                return back()->withErrors([
                    'amount' => "Payment amount (₹" . number_format($amount, 2) . ") exceeds remaining balance due on PO #{$po->po_number} (₹" . number_format($po->balanceDue(), 2) . ")."
                ])->withInput();
            }
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($supplier, $validated, $amount) {
            $po = null;
            if (!empty($validated['purchase_order_id'])) {
                $po = \App\Models\PurchaseOrder::where('supplier_id', $supplier->id)->find($validated['purchase_order_id']);
            }

            \App\Models\VendorPayment::create([
                'supplier_id'           => $supplier->id,
                'purchase_order_id'     => $po?->id,
                'payment_reference'     => \App\Models\VendorPayment::generatePaymentReference(),
                'payment_date'          => $validated['payment_date'],
                'amount'                => $amount,
                'payment_method'        => $validated['payment_method'],
                'transaction_reference' => $validated['transaction_reference'] ?? null,
                'notes'                 => $validated['notes'] ?? null,
                'created_by'            => auth()->id(),
            ]);

            if ($po) {
                $newPaid = (float) $po->amount_paid + $amount;
                $status = 'unpaid';
                if ($newPaid >= (float) $po->total) {
                    $status = 'paid';
                } elseif ($newPaid > 0) {
                    $status = 'partially_paid';
                }
                $po->update([
                    'amount_paid'    => $newPaid,
                    'payment_status' => $status,
                ]);
            }
        });

        return back()->with('status', 'Offline payment record saved and vendor ledger updated successfully.');
    }

    public function edit(Supplier $supplier): View
    {
        $this->authorize('update', $supplier);
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(StoreSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $this->authorize('update', $supplier);
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        $supplier->update($validated);

        return redirect()->route('suppliers.show', $supplier)->with('status', "Supplier '{$supplier->name}' updated successfully.");
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->authorize('delete', $supplier);

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('status', 'Supplier record deleted.');
    }

    /**
     * Suggest preferred suppliers based on product category or item name.
     */
    public function suggest(Request $request): JsonResponse
    {
        $this->authorize('suggest', Supplier::class);
        $category = $request->input('category');
        $itemName = $request->input('item_name');

        $detectedCategory = Supplier::mapProductCategoryToSupplierCategory($category, $itemName);
        $suppliers = Supplier::getSuggestedSuppliers($category, $itemName);

        return response()->json([
            'detected_category' => $detectedCategory,
            'suppliers' => $suppliers->map(function ($s) {
                return [
                    'id'              => $s->id,
                    'name'            => $s->name,
                    'company_name'    => $s->company_name,
                    'phone'           => $s->phone,
                    'categories'      => $s->categories ?? [],
                    'brands_supplied' => $s->brands_supplied,
                ];
            })
        ]);
    }

    /**
     * Share WhatsApp communique with Supplier / Vendor.
     */
    public function shareWhatsApp(Request $request, Supplier $supplier): RedirectResponse|JsonResponse
    {
        $this->authorize('view', $supplier);
        $validated = $request->validate([
            'phone'             => ['nullable', 'string', 'max:25'],
            'message'           => ['nullable', 'string', 'max:5000'],
            'template_type'     => ['nullable', 'in:inquiry,payment,stock_check,stock_inquiry,po_dispatch,payment_confirmation'],
            'purchase_order_id' => ['nullable', 'exists:purchase_orders,id'],
            'vendor_payment_id' => ['nullable', 'exists:vendor_payments,id'],
        ]);

        $phone = $validated['phone'] ?: ($supplier->phone ?? '');
        $templateType = $validated['template_type'] ?? 'inquiry';
        $customMessage = $validated['message'] ?? null;

        // Route to specialized generator if specific PO or Payment is selected
        if (!empty($validated['vendor_payment_id'])) {
            $payment = \App\Models\VendorPayment::with(['supplier', 'purchaseOrder'])->find($validated['vendor_payment_id']);
            if ($payment) {
                $messageText = $payment->buildWhatsAppMessage($customMessage);
                $whatsappUrl = $payment->getWhatsAppUrl($phone, $customMessage);
            } else {
                $messageText = $supplier->buildWhatsAppMessage($customMessage, $templateType);
                $whatsappUrl = $supplier->getWhatsAppUrl($phone, $customMessage, $templateType);
            }
        } elseif (!empty($validated['purchase_order_id']) && $templateType === 'po_dispatch') {
            $po = \App\Models\PurchaseOrder::with(['supplier', 'items'])->find($validated['purchase_order_id']);
            if ($po) {
                $publicPdfUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute('purchase-orders.public-pdf', now()->addDays(14), ['purchase_order' => $po]);
                $messageText = $po->buildWhatsAppMessage($customMessage, $publicPdfUrl);
                $whatsappUrl = $po->getWhatsAppUrl($phone, $customMessage, $publicPdfUrl);
            } else {
                $messageText = $supplier->buildWhatsAppMessage($customMessage, $templateType);
                $whatsappUrl = $supplier->getWhatsAppUrl($phone, $customMessage, $templateType);
            }
        } else {
            $messageText = $supplier->buildWhatsAppMessage($customMessage, $templateType);
            $whatsappUrl = $supplier->getWhatsAppUrl($phone, $customMessage, $templateType);
        }

        // Record in Notification Log for full auditability
        try {
            \App\Models\NotificationLog::create([
                'channel'         => 'whatsapp',
                'event_type'      => 'vendor_communique',
                'recipient_type'  => 'vendor',
                'recipient_name'  => $supplier->company_name ?: $supplier->name,
                'recipient_phone' => $phone,
                'recipient_email' => $supplier->email,
                'subject'         => "WhatsApp to {$supplier->name} (" . ucfirst(str_replace('_', ' ', $templateType)) . ")",
                'message_body'    => $messageText,
                'action_url'      => route('suppliers.show', $supplier),
                'status'          => 'sent',
                'reference_type'  => Supplier::class,
                'reference_id'    => $supplier->id,
                'sent_at'         => now(),
                'created_by'      => auth()->id(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Supplier WhatsApp log failed: " . $e->getMessage());
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'whatsapp_url' => $whatsappUrl,
                'phone'        => $phone,
                'message'      => $messageText,
            ]);
        }

        return redirect()->away($whatsappUrl);
    }

    /**
     * Send official email to Supplier / Vendor.
     */
    public function sendEmail(Request $request, Supplier $supplier): RedirectResponse
    {
        $this->authorize('view', $supplier);
        $validated = $request->validate([
            'email'             => ['required', 'email'],
            'subject'           => ['required', 'string', 'max:255'],
            'message_body'      => ['required', 'string', 'max:5000'],
            'template_type'     => ['nullable', 'string', 'max:50'],
            'purchase_order_id' => ['nullable', 'exists:purchase_orders,id'],
            'vendor_payment_id' => ['nullable', 'exists:vendor_payments,id'],
        ]);

        $recipientEmail = $validated['email'];
        $subject = $validated['subject'];
        $bodyText = $validated['message_body'];
        $templateType = $validated['template_type'] ?? 'inquiry';

        // Check if specialized HTML rendering applies
        if (!empty($validated['vendor_payment_id'])) {
            $payment = \App\Models\VendorPayment::with(['supplier', 'purchaseOrder'])->find($validated['vendor_payment_id']);
            if ($payment) {
                $styledBody = $payment->buildEmailHtml($bodyText);
            }
        } elseif (!empty($validated['purchase_order_id']) && $templateType === 'po_dispatch') {
            $po = \App\Models\PurchaseOrder::with(['supplier', 'items'])->find($validated['purchase_order_id']);
            if ($po) {
                $itemsHtml = '';
                foreach ($po->items as $idx => $item) {
                    $num = $idx + 1;
                    $itemsHtml .= "
                        <tr style='border-bottom: 1px solid #f1f5f9;'>
                            <td style='padding: 8px 10px; text-align: center; color: #64748b;'>{$num}</td>
                            <td style='padding: 8px 10px; color: #0f172a; font-weight: bold;'>
                                " . e($item->item_name) . "
                                " . ($item->sku ? "<br><span style='font-size: 11px; color: #64748b; font-family: monospace;'>SKU: " . e($item->sku) . "</span>" : "") . "
                            </td>
                            <td style='padding: 8px 10px; text-align: right;'>" . (int) $item->quantity_ordered . "</td>
                            <td style='padding: 8px 10px; text-align: right;'>₹" . number_format((float) $item->unit_cost, 2) . "</td>
                            <td style='padding: 8px 10px; text-align: right; font-weight: bold;'>₹" . number_format((float) ($item->total_cost ?: ($item->quantity_ordered * $item->unit_cost)), 2) . "</td>
                        </tr>";
                }
                $publicPdfUrl = \Illuminate\Support\Facades\URL::signedRoute('purchase-orders.public-pdf', ['purchase_order' => $po]);
                $styledBody = "
                    <div style='font-family: Arial, sans-serif; font-size: 14px; color: #1e293b; max-width: 680px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;'>
                        <div style='background-color: #0f172a; padding: 20px 24px; color: #ffffff;'>
                            <table style='width: 100%;'>
                                <tr>
                                    <td>
                                        <h2 style='margin: 0; font-size: 18px; color: #38bdf8;'>Precision IT Systems</h2>
                                        <p style='margin: 4px 0 0 0; font-size: 12px; color: #94a3b8;'>Purchase Order & Supply Chain Operations</p>
                                    </td>
                                    <td style='text-align: right;'>
                                        <span style='background-color: #2563eb; color: #ffffff; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 12px; font-family: monospace;'>PO #{$po->po_number}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div style='padding: 24px; line-height: 1.6; background-color: #ffffff;'>
                            <p style='margin-top: 0;'>Dear <strong>" . e($supplier->contact_person ?: ($supplier->company_name ?: $supplier->name)) . "</strong>,</p>
                            <p>Please find our official Purchase Order details below. Kindly acknowledge receipt and confirm expected delivery schedule.</p>
                            <div style='margin: 14px 0; padding: 12px 14px; background-color: #f8fafc; border-left: 4px solid #3b82f6; font-size: 13px;'>" . nl2br(e($bodyText)) . "</div>

                            <table style='width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 13px;'>
                                <thead>
                                    <tr style='background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569;'>
                                        <th style='padding: 8px 10px; text-align: center;'>#</th>
                                        <th style='padding: 8px 10px; text-align: left;'>Item Description</th>
                                        <th style='padding: 8px 10px; text-align: right;'>Qty</th>
                                        <th style='padding: 8px 10px; text-align: right;'>Unit Rate</th>
                                        <th style='padding: 8px 10px; text-align: right;'>Total</th>
                                    </tr>
                                </thead>
                                <tbody>{$itemsHtml}</tbody>
                                <tfoot>
                                    <tr style='border-top: 2px solid #e2e8f0; font-weight: bold; background-color: #f8fafc;'>
                                        <td colspan='4' style='padding: 10px; text-align: right;'>Grand Total:</td>
                                        <td style='padding: 10px; text-align: right; color: #2563eb;'>₹" . number_format((float) $po->total, 2) . "</td>
                                    </tr>
                                </tfoot>
                            </table>

                            <div style='text-align: center; margin: 24px 0;'>
                                <a href='{$publicPdfUrl}' style='display: inline-block; background-color: #0f172a; color: #ffffff; padding: 10px 22px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 13px;'>Download Official Signed PO PDF</a>
                            </div>

                            <div style='margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b;'>
                                <strong>Precision IT Systems — Commercial & Surveillance Division</strong><br>
                                Phone: +91 96772 57774 | Email: operations@precisionit.co.in<br>
                                GSTIN: 33AAACP1234A1Z5
                            </div>
                        </div>
                    </div>";
            }
        }

        if (!isset($styledBody)) {
            $htmlBody = nl2br(e($bodyText));
            $headerTitle = $templateType === 'stock_inquiry' ? 'Stock Availability & Price Quotation Request' : 'Procurement & Vendor Operations';
            $headerColor = $templateType === 'stock_inquiry' ? '#0284c7' : '#38bdf8';
            $styledBody = "
                <div style='font-family: Arial, sans-serif; font-size: 14px; color: #1e293b; max-width: 650px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;'>
                    <div style='background-color: #0f172a; padding: 20px 24px; color: #ffffff;'>
                        <h2 style='margin: 0; font-size: 18px; color: {$headerColor};'>Precision IT Systems</h2>
                        <p style='margin: 4px 0 0 0; font-size: 12px; color: #94a3b8;'>{$headerTitle}</p>
                    </div>
                    <div style='padding: 24px; line-height: 1.6; background-color: #ffffff;'>
                        <p style='margin-top: 0;'>Dear <strong>" . e($supplier->contact_person ?: ($supplier->company_name ?: $supplier->name)) . "</strong>,</p>
                        <div style='margin: 16px 0;'>" . $htmlBody . "</div>
                        <div style='margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b;'>
                            <strong>Precision IT Systems — Commercial & Surveillance Division</strong><br>
                            Phone: +91 96772 57774 | Email: operations@precisionit.co.in<br>
                            GSTIN: 33AAACP1234A1Z5
                        </div>
                    </div>
                </div>
            ";
        }

        try {
            \App\Services\MailConfigService::applyDynamicMailConfig();
            \Illuminate\Support\Facades\Mail::html($styledBody, function ($msg) use ($recipientEmail, $supplier, $subject) {
                $msg->to($recipientEmail, $supplier->name)->subject($subject);
            });
            $status = 'sent';
            $error = null;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Supplier Email dispatch failed: " . $e->getMessage());
            $status = 'failed';
            $error = $e->getMessage();
        }

        // Record in Notification Log
        try {
            \App\Models\NotificationLog::create([
                'channel'         => 'email',
                'event_type'      => 'vendor_email',
                'recipient_type'  => 'vendor',
                'recipient_name'  => $supplier->company_name ?: $supplier->name,
                'recipient_phone' => $supplier->phone,
                'recipient_email' => $recipientEmail,
                'subject'         => $subject,
                'message_body'    => $bodyText,
                'action_url'      => route('suppliers.show', $supplier),
                'status'          => $status,
                'error_message'   => $error,
                'reference_type'  => Supplier::class,
                'reference_id'    => $supplier->id,
                'sent_at'         => now(),
                'created_by'      => auth()->id(),
            ]);
        } catch (\Throwable $e) {
            // Log fallback
        }

        if ($status === 'failed') {
            return back()->with('error', "Email dispatch failed: {$error}");
        }

        return back()->with('status', "✅ Email dispatched successfully to {$supplier->name} ({$recipientEmail}).");
    }

    /**
     * Share Payment Advice with Supplier via WhatsApp directly from Payment Record.
     */
    public function sharePaymentWhatsApp(Request $request, \App\Models\VendorPayment $payment): RedirectResponse|JsonResponse
    {
        $payment->load(['supplier', 'purchaseOrder']);
        $supplier = $payment->supplier;
        $phone = $request->input('phone') ?: ($supplier?->phone ?? '');
        $customNote = $request->input('custom_note');

        $messageText = $payment->buildWhatsAppMessage($customNote);
        $whatsappUrl = $payment->getWhatsAppUrl($phone, $customNote);

        try {
            \App\Models\NotificationLog::create([
                'channel'         => 'whatsapp',
                'event_type'      => 'payment_advice',
                'recipient_type'  => 'vendor',
                'recipient_name'  => $supplier?->company_name ?: ($supplier?->name ?? 'Vendor'),
                'recipient_phone' => $phone,
                'recipient_email' => $supplier?->email,
                'subject'         => "Payment Confirmation & Advice: {$payment->payment_reference}",
                'message_body'    => $messageText,
                'action_url'      => $supplier ? route('suppliers.show', $supplier) : null,
                'status'          => 'sent',
                'reference_type'  => \App\Models\VendorPayment::class,
                'reference_id'    => $payment->id,
                'sent_at'         => now(),
                'created_by'      => auth()->id(),
            ]);
        } catch (\Throwable $e) {}

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'      => true,
                'whatsapp_url' => $whatsappUrl,
                'phone'        => $phone,
                'message'      => $messageText,
            ]);
        }

        return redirect()->away($whatsappUrl);
    }

    /**
     * Send official Payment Advice Email to Supplier directly from Payment Record.
     */
    public function sendPaymentEmail(Request $request, \App\Models\VendorPayment $payment): RedirectResponse
    {
        $payment->load(['supplier', 'purchaseOrder']);
        $supplier = $payment->supplier;
        $recipientEmail = $request->input('email') ?: ($supplier?->email ?? '');

        if (empty($recipientEmail)) {
            return back()->with('error', 'Please enter a valid recipient email address.');
        }

        $customNote = $request->input('custom_note');
        $subject = $request->input('subject') ?: $payment->buildEmailSubject();
        $styledBody = $payment->buildEmailHtml($customNote);

        try {
            \App\Services\MailConfigService::applyDynamicMailConfig();
            \Illuminate\Support\Facades\Mail::html($styledBody, function ($msg) use ($recipientEmail, $supplier, $subject) {
                $msg->to($recipientEmail, $supplier?->name ?? 'Vendor')->subject($subject);
            });
            $status = 'sent';
            $error = null;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Payment Advice Email failed: " . $e->getMessage());
            $status = 'failed';
            $error = $e->getMessage();
        }

        try {
            \App\Models\NotificationLog::create([
                'channel'         => 'email',
                'event_type'      => 'payment_advice',
                'recipient_type'  => 'vendor',
                'recipient_name'  => $supplier?->company_name ?: ($supplier?->name ?? 'Vendor'),
                'recipient_phone' => $supplier?->phone,
                'recipient_email' => $recipientEmail,
                'subject'         => $subject,
                'message_body'    => strip_tags(str_replace('<br>', "\n", $styledBody)),
                'action_url'      => $supplier ? route('suppliers.show', $supplier) : null,
                'status'          => $status,
                'error_message'   => $error,
                'reference_type'  => \App\Models\VendorPayment::class,
                'reference_id'    => $payment->id,
                'sent_at'         => now(),
                'created_by'      => auth()->id(),
            ]);
        } catch (\Throwable $e) {}

        if ($status === 'failed') {
            return back()->with('error', "Email dispatch failed: {$error}");
        }

        return back()->with('status', "✅ Payment remittance advice emailed successfully to {$recipientEmail}.");
    }
}
