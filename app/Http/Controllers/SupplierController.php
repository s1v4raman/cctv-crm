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
        if (auth()->user()?->role === 'technician') {
            abort(403, 'Technicians are not authorized to view supplier records.');
        }
        $search = trim((string) $request->input('search', ''));
        $query = Supplier::query()->withCount('purchaseOrders');
        if (in_array($request->input('active'), ['yes', 'no'], true)) {
            $query->where('is_active', $request->input('active') === 'yes');
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
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

        return view('suppliers.index', compact('suppliers', 'totalSuppliers', 'activeSuppliers'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(StoreSupplierRequest $request): RedirectResponse|JsonResponse
    {
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
        $validated = $request->validate([
            'purchase_order_id'     => ['nullable', 'exists:purchase_orders,id'],
            'amount'                => ['required', 'numeric', 'min:0.01'],
            'payment_date'          => ['required', 'date'],
            'payment_method'        => ['required', 'in:neft_rtgs,bank_transfer,cheque,upi,cash'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'notes'                 => ['nullable', 'string', 'max:1000'],
        ]);

        $amount = (float) $validated['amount'];

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
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(StoreSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        $supplier->update($validated);

        return redirect()->route('suppliers.show', $supplier)->with('status', "Supplier '{$supplier->name}' updated successfully.");
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('status', 'Supplier record deleted.');
    }

    /**
     * Share WhatsApp communique with Supplier / Vendor.
     */
    public function shareWhatsApp(Request $request, Supplier $supplier): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'phone'         => ['nullable', 'string', 'max:25'],
            'message'       => ['nullable', 'string', 'max:2000'],
            'template_type' => ['nullable', 'in:inquiry,payment,stock_check'],
        ]);

        $phone = $validated['phone'] ?: ($supplier->phone ?? '');
        $templateType = $validated['template_type'] ?? 'inquiry';
        $customMessage = $validated['message'] ?? null;

        $messageText = $supplier->buildWhatsAppMessage($customMessage, $templateType);
        $whatsappUrl = $supplier->getWhatsAppUrl($phone, $customMessage, $templateType);

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
        $validated = $request->validate([
            'email'        => ['required', 'email'],
            'subject'      => ['required', 'string', 'max:255'],
            'message_body' => ['required', 'string', 'max:5000'],
        ]);

        $recipientEmail = $validated['email'];
        $subject = $validated['subject'];
        $bodyText = $validated['message_body'];

        // Format HTML Email
        $htmlBody = nl2br(e($bodyText));
        $styledBody = "
            <div style='font-family: Arial, sans-serif; font-size: 14px; color: #1e293b; max-width: 650px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;'>
                <div style='background-color: #0f172a; padding: 20px 24px; color: #ffffff;'>
                    <h2 style='margin: 0; font-size: 18px; color: #38bdf8;'>Precision IT Systems</h2>
                    <p style='margin: 4px 0 0 0; font-size: 12px; color: #94a3b8;'>Procurement & Vendor Operations</p>
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
}
