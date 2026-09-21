<?php

use App\Http\Controllers\InstallationJobController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuotationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\AmcContractController;
use App\Http\Controllers\Technician\TechnicianPortalController;
use App\Http\Controllers\SiteSurveyController;
use App\Http\Controllers\ServiceTicketController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InstalledEquipmentController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\CctvEstimatorController;
use App\Http\Controllers\Customer\CustomerPortalController;
use App\Http\Controllers\JobCompletionReportController;
use App\Http\Controllers\RmaClaimController;
use App\Http\Controllers\AlertNotificationController;
use App\Http\Controllers\ExecutiveAnalyticsController;
use App\Http\Controllers\InboundEmailSupportController;
use App\Http\Controllers\Api\EquipmentLookupController;
use App\Http\Controllers\OnlinePaymentController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\MobileScannerSyncController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\FinanceController;

// Mobile Phone Remote Barcode Scanner Companion & Live Sync
Route::get('/mobile-scanner/{token?}', [MobileScannerSyncController::class, 'show'])->name('mobile.scanner');
Route::post('/api/mobile-scanner/push', [MobileScannerSyncController::class, 'push'])->name('api.mobile-scanner.push');
Route::get('/api/mobile-scanner/poll/{token}', [MobileScannerSyncController::class, 'poll'])->name('api.mobile-scanner.poll');
Route::post('/api/mobile-scanner/heartbeat/{token}', [MobileScannerSyncController::class, 'heartbeat'])->name('api.mobile-scanner.heartbeat');
Route::get('/api/mobile-scanner/lan-info', [MobileScannerSyncController::class, 'getLanInfo'])->name('api.mobile-scanner.lan-info');

// Universal Global Omnisearch API
Route::get('/api/global-search', [\App\Http\Controllers\Api\GlobalSearchController::class, 'search'])->name('api.global-search');

// Public Amazon-style CCTV Storefront Homepage & Inquiry Engine
Route::get('/', [StorefrontController::class, 'index'])->name('home');
Route::post('/public/inquire', [StorefrontController::class, 'inquire'])->name('public.inquire');

// Inbound Email Webhook (Handles customer emails from Mailgun, SendGrid, Postmark, AWS SES, or custom webhook)
Route::post('/api/inbound-email', [InboundEmailSupportController::class, 'webhook'])
    ->name('api.inbound-email');
Route::post('/webhook/support-email', [InboundEmailSupportController::class, 'webhook'])
    ->name('webhook.support-email');

// Razorpay Asynchronous Payment Webhook
Route::post('/webhook/razorpay', [OnlinePaymentController::class, 'webhook'])
    ->name('webhook.razorpay');

// Public Online Payment Checkout Gateway (Invoices & Quotation Advances)
Route::get('/pay/invoice/{invoice}', [OnlinePaymentController::class, 'checkoutInvoice'])
    ->name('payment.checkout.invoice');
Route::get('/pay/quotation/{quotation}', [OnlinePaymentController::class, 'checkoutQuotation'])
    ->name('payment.checkout.quotation');
Route::post('/api/payment/create-order', [OnlinePaymentController::class, 'createOrder'])
    ->name('payment.create-order');
Route::post('/api/payment/verify', [OnlinePaymentController::class, 'verifyPayment'])
    ->name('payment.verify');

// Public Official Payment Receipt PDF (Downloaded via WhatsApp / Email links)
Route::get('/payments/{payment}/receipt', [OnlinePaymentController::class, 'downloadReceipt'])
    ->name('payments.receipt.pdf');

// Public customer Quotation PDF viewer (accessible from WhatsApp links)
Route::get('/view-quotation/{quotation}/{format?}', [QuotationController::class, 'publicPdf'])
    ->name('quotations.public-pdf');

// Public customer JCR PDF viewer (accessible from WhatsApp links)
Route::get('/view-jcr/{jobCompletionReport}', [JobCompletionReportController::class, 'publicPdf'])
    ->name('jcr.public-pdf');

Route::middleware(['auth', 'verified'])->group(function () {

    // Profile management (all authenticated users)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Route group for Admins Only (Write/Manage actions)
    Route::middleware(['admin'])->group(function () {

        // Leads Write
        Route::get('/leads/create', [LeadController::class, 'create'])->name('leads.create');
        Route::post('/leads', [LeadController::class, 'store'])->name('leads.store');
        Route::get('/leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
        Route::put('/leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
        Route::delete('/leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
        Route::post('/leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.status');

        // Jobs Write
        Route::get('/jobs/create', [InstallationJobController::class, 'createGeneral'])->name('jobs.create-general');
        Route::post('/quotations/{quotation}/job', [InstallationJobController::class, 'store'])->name('jobs.store');
        Route::patch('/jobs/{job}', [InstallationJobController::class, 'update'])->name('jobs.update');

        // Products Write
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');

        // Inventory Stock Adjustments (Admin)
        Route::post('/inventory/{product}/adjust', [InventoryController::class, 'adjustStock'])->name('inventory.adjust');

        // Installed Equipment Delete (Admin)
        Route::delete('/equipment/{equipment}', [InstalledEquipmentController::class, 'destroy'])->name('equipment.destroy');

        // Purchase Orders Delete (Admin)
        Route::delete('/purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'destroy'])->name('purchase-orders.destroy');

        // User Accounts CRUD
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        });
    });

    // Route group for Internal Users (Admin + Staff/User)
    Route::middleware(['internal'])->group(function () {

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');

        // Operations Calendar & Dispatch
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
        Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');

        // Leads (Read-Only)
        Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
        Route::get('/leads/{lead}/vcard', [LeadController::class, 'downloadVcard'])->name('leads.vcard');

        // Quotations & Items (Full Access to create & manage)
        Route::get('/leads/{lead}/quote', [QuotationController::class, 'create'])->name('quotations.create');
        Route::post('/leads/{lead}/quote', [QuotationController::class, 'store'])->name('quotations.store');
        Route::get('/quotations/create', [QuotationController::class, 'createGeneral'])->name('quotations.create-general');
        Route::get('/quotations', [QuotationController::class, 'index'])->name('quotations.index');
        Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
        Route::get('/quotations/{quotation}/edit', [QuotationController::class, 'edit'])->name('quotations.edit');
        Route::put('/quotations/{quotation}', [QuotationController::class, 'update'])->name('quotations.update');
        Route::delete('/quotations/{quotation}', [QuotationController::class, 'destroy'])->name('quotations.destroy');
        Route::post('/quotations/{quotation}/accept', [QuotationController::class, 'markAccepted'])->name('quotations.accept');
        Route::post('/quotations/{quotation}/reject', [QuotationController::class, 'markRejected'])->name('quotations.reject');
        Route::patch('/quotations/{quotation}/sent', [QuotationController::class, 'markSent'])->name('quotations.markSent');
        Route::get('/quotations/{quotation}/send-whatsapp', [QuotationController::class, 'sendWhatsApp'])->name('quotations.send-whatsapp');
        Route::post('/quotations/{quotation}/send-email', [QuotationController::class, 'sendEmail'])->name('quotations.send-email');
        Route::get('/quotations/{quotation}/pdf/{format}', [QuotationController::class, 'downloadPdf'])->name('quotations.pdf');
        Route::get('/quotations/{quotation}/vcard', [QuotationController::class, 'downloadVcard'])->name('quotations.vcard');
        Route::delete('/quotation-items/{item}', [QuotationController::class, 'destroyItem'])->name('quotation-items.destroy');

        // CCTV Storage & Cable Estimator / Auto-BOM Engine
        Route::get('/estimator', [CctvEstimatorController::class, 'index'])->name('estimator.index');
        Route::post('/estimator/convert-quotation', [CctvEstimatorController::class, 'convertToQuotation'])->name('estimator.convert');

        // Jobs (Read-Only)
        Route::get('/jobs', [InstallationJobController::class, 'index'])->name('jobs.index');
        Route::get('/jobs/{job}', [InstallationJobController::class, 'show'])->name('jobs.show');

        // Products Catalog (Read-Only)
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');

        // Inventory Management (Stock Overview & Audit Log)
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');

        // Installed Equipment & Warranty Tracking (Admin & Staff API)
        Route::get('/api/equipment/lookup-serial', [\App\Http\Controllers\Api\EquipmentLookupController::class, 'lookup'])->name('equipment.lookup');

        // RMA & Warranty Claims / Vendor Replacement
        Route::resource('rma', RmaClaimController::class);
        Route::post('/rma/{rma}/dispatch', [RmaClaimController::class, 'dispatchToVendor'])->name('rma.dispatch');
        Route::post('/rma/{rma}/vendor-status', [RmaClaimController::class, 'updateVendorStatus'])->name('rma.vendor-status');
        Route::post('/rma/{rma}/resolution', [RmaClaimController::class, 'recordResolution'])->name('rma.resolution');
        Route::get('/rma/{rma}/dispatch-pdf', [RmaClaimController::class, 'downloadDispatchChallan'])->name('rma.dispatch-pdf');

        // Suppliers & Vendors Procurement
        Route::resource('suppliers', SupplierController::class);

        // Purchase Orders (PO) & Stock Inwarding
        Route::resource('purchase-orders', PurchaseOrderController::class)->except(['destroy']);
        Route::post('/purchase-orders/{purchase_order}/receive', [PurchaseOrderController::class, 'receiveItems'])->name('purchase-orders.receive');
        Route::patch('/purchase-orders/{purchase_order}/status', [PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.updateStatus');
        Route::patch('/purchase-orders/{purchase_order}/payment', [PurchaseOrderController::class, 'updatePayment'])->name('purchase-orders.updatePayment');

        // Invoices & Payments (Internal CRM actions)
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/jobs/{job}/invoice/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/jobs/{job}/invoice', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::post('/invoices/{invoice}/payments', [InvoiceController::class, 'storePayment'])->name('payments.store');
        Route::delete('/payments/{payment}', [InvoiceController::class, 'destroyPayment'])->name('payments.destroy');

        // AMC Contracts & Visits
        Route::get('/amcs', [AmcContractController::class, 'index'])->name('amcs.index');
        Route::get('/amcs/create', [AmcContractController::class, 'create'])->name('amcs.create');
        Route::post('/amcs', [AmcContractController::class, 'store'])->name('amcs.store');
        Route::get('/amcs/{amc}', [AmcContractController::class, 'show'])->name('amcs.show');
        Route::patch('/amcs/{amc}/status', [AmcContractController::class, 'updateStatus'])->name('amcs.updateStatus');
        Route::delete('/amcs/{amc}', [AmcContractController::class, 'destroy'])->name('amcs.destroy');
        Route::post('/amc-visits/{visit}/assign', [AmcContractController::class, 'assignVisitTechnician'])->name('amc-visits.assign');
        Route::post('/amc-visits/{visit}/complete', [AmcContractController::class, 'completeVisit'])->name('amc-visits.complete');

        // Site Surveys
        Route::resource('site-surveys', SiteSurveyController::class);
        Route::delete('/site-surveys/photos/{photo}', [SiteSurveyController::class, 'destroyPhoto'])
            ->name('site-surveys.photos.destroy');

        // Service Tickets & Helpdesk
        Route::get('/service-tickets/export', [ServiceTicketController::class, 'exportCsv'])
            ->name('service-tickets.export');
        Route::get('/service-tickets/export-pdf', [ServiceTicketController::class, 'exportPdf'])
            ->name('service-tickets.export-pdf');
        Route::get('/service-tickets/lead-amc/{lead}', [ServiceTicketController::class, 'getLeadAmc'])
            ->name('service-tickets.lead-amc');
        Route::resource('service-tickets', ServiceTicketController::class);
        Route::patch('/service-tickets/{service_ticket}/status', [ServiceTicketController::class, 'updateStatus'])
            ->name('service-tickets.updateStatus');
        Route::post('/service-tickets/{service_ticket}/assign', [ServiceTicketController::class, 'assignTechnician'])
            ->name('service-tickets.assign');
        Route::post('/service-tickets/{service_ticket}/accept', [ServiceTicketController::class, 'acceptRequest'])
            ->name('service-tickets.accept');
        Route::post('/service-tickets/{service_ticket}/reject', [ServiceTicketController::class, 'rejectRequest'])
            ->name('service-tickets.reject');
        Route::post('/service-tickets/simulate-email', [InboundEmailSupportController::class, 'simulate'])
            ->name('service-tickets.simulate-email');

        // Digital Job Completion Reports (JCR) Index (Admin & Staff)
        Route::get('/jcr', [JobCompletionReportController::class, 'index'])->name('jcr.index');

        // Automated Alerts & Notification Engine Hub
        Route::get('/alerts', [AlertNotificationController::class, 'index'])->name('alerts.index');
        Route::get('/alerts/templates', [AlertNotificationController::class, 'templates'])->name('alerts.templates');
        Route::put('/alerts/templates/{template}', [AlertNotificationController::class, 'updateTemplate'])->name('alerts.templates.update');
        Route::get('/alerts/gateways', [AlertNotificationController::class, 'gateways'])->name('alerts.gateways');
        Route::put('/alerts/gateways', [AlertNotificationController::class, 'updateGateways'])->name('alerts.gateways.update');
        Route::post('/alerts/gateways/test-sms', [AlertNotificationController::class, 'testSmsGateway'])->name('alerts.gateways.test-sms');
        Route::post('/alerts/gateways/test-otp', [AlertNotificationController::class, 'testOtp'])->name('alerts.gateways.test-otp');
        Route::post('/alerts/gateways/test-email', [AlertNotificationController::class, 'testEmailGateway'])->name('alerts.gateways.test-email');
        Route::post('/alerts/broadcast', [AlertNotificationController::class, 'sendManualBroadcast'])->name('alerts.broadcast');
        Route::post('/alerts/run-sweep', [AlertNotificationController::class, 'runAutomatedSweep'])->name('alerts.run-sweep');

        // Executive Business Analytics & Profitability Dashboard
        Route::get('/analytics', [ExecutiveAnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/analytics/api', [ExecutiveAnalyticsController::class, 'apiData'])->name('analytics.api');
        Route::get('/analytics/export-pdf', [ExecutiveAnalyticsController::class, 'exportPdf'])->name('analytics.export-pdf');
        Route::get('/analytics/cost-profit', [ExecutiveAnalyticsController::class, 'costProfit'])->name('analytics.cost-profit');
        Route::get('/analytics/cost-profit/export-pdf', [ExecutiveAnalyticsController::class, 'exportCostProfitPdf'])->name('analytics.cost-profit.export-pdf');

        // Technician Performance Dashboard (FTFR, MTTR, Completed Installations)
        Route::get('/analytics/technicians', [ExecutiveAnalyticsController::class, 'technicians'])->name('analytics.technicians');
        Route::get('/analytics/technicians/export-pdf', [ExecutiveAnalyticsController::class, 'exportTechniciansPdf'])->name('analytics.technicians.export-pdf');
        Route::get('/analytics/api/technicians', [ExecutiveAnalyticsController::class, 'apiTechnicians'])->name('analytics.api.technicians');

        // Monthly Recurring Revenue & AMC Retention Report
        Route::get('/analytics/mrr-retention', [ExecutiveAnalyticsController::class, 'mrrRetention'])->name('analytics.mrr-retention');
        Route::get('/analytics/mrr-retention/export-pdf', [ExecutiveAnalyticsController::class, 'exportMrrRetentionPdf'])->name('analytics.mrr-retention.export-pdf');
        Route::get('/analytics/api/mrr-retention', [ExecutiveAnalyticsController::class, 'apiMrrRetention'])->name('analytics.api.mrr-retention');

        // Employee Attendance Management System
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
        Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::post('/attendance/quick-mark', [AttendanceController::class, 'quickMark'])->name('attendance.quick-mark');
        Route::post('/attendance/batch-store', [AttendanceController::class, 'batchStore'])->name('attendance.batch-store');
        Route::put('/attendance/{attendance}', [AttendanceController::class, 'update'])->name('attendance.update');

        // Finance & Salary Management (Monthly, Weekly, Per-Day)
        Route::get('/finance/salaries', [FinanceController::class, 'salaries'])->name('finance.salaries.index');
        Route::post('/finance/salaries/{user}', [FinanceController::class, 'updateSalary'])->name('finance.salaries.update');
        Route::get('/finance/payroll', [FinanceController::class, 'payrollIndex'])->name('finance.payroll.index');
        Route::post('/finance/payroll/generate', [FinanceController::class, 'generatePayroll'])->name('finance.payroll.generate');
        Route::get('/finance/payroll/{payroll}', [FinanceController::class, 'showPayroll'])->name('finance.payroll.show');
        Route::post('/finance/payroll/{payroll}/status', [FinanceController::class, 'updatePayrollStatus'])->name('finance.payroll.updateStatus');
        Route::get('/finance/analytics', [FinanceController::class, 'analytics'])->name('finance.analytics');

        // Documentation & Architecture Data Flow PDF
        Route::get('/docs/data-flow-diagram', function () {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('docs.data_flow_pdf');
            return $pdf->stream('CCTV_CRM_Module_Data_Flow_Diagram.pdf');
        })->name('docs.data-flow-pdf');
    });

    // JCR Sign-off Wizard & Report Views (Technicians + Internal Staff)
    Route::get('/jcr/{jobCompletionReport}', [JobCompletionReportController::class, 'show'])->name('jcr.show');
    Route::get('/jcr/{jobCompletionReport}/pdf', [JobCompletionReportController::class, 'downloadPdf'])->name('jcr.download-pdf');
    Route::get('/jobs/{job}/sign-off', [JobCompletionReportController::class, 'createForJob'])->name('jcr.create-job');
    Route::get('/service-tickets/{ticket}/sign-off', [JobCompletionReportController::class, 'createForTicket'])->name('jcr.create-ticket');
    Route::get('/amc-visits/{visit}/sign-off', [JobCompletionReportController::class, 'createForAmcVisit'])->name('jcr.create-visit');
    Route::post('/jcr', [JobCompletionReportController::class, 'store'])->name('jcr.store');

    // Installed Equipment & Hardware Registry (Technicians + Internal Staff)
    Route::resource('equipment', InstalledEquipmentController::class)->except(['destroy']);

    // Barcode & Serial Number Instant Lookup API
    Route::get('/api/equipment/lookup-serial', [EquipmentLookupController::class, 'lookup'])->name('equipment.lookup');
    Route::get('/api/equipment/lookup', [EquipmentLookupController::class, 'lookup'])->name('api.equipment.lookup-serial');
});

Route::middleware(['auth', 'technician'])->prefix('technician')->name('technician.')->group(function () {
    Route::get('/dashboard', [TechnicianPortalController::class, 'dashboard'])->name('dashboard');
    Route::post('/jobs/{job}/status', [TechnicianPortalController::class, 'updateJobStatus'])->name('jobs.updateStatus');
    Route::post('/amc-visits/{visit}/complete', [TechnicianPortalController::class, 'completeAmcVisit'])->name('amc-visits.complete');
    Route::post('/service-tickets/{ticket}/update', [TechnicianPortalController::class, 'updateServiceTicket'])->name('service-tickets.update');
    Route::get('/surveys/{siteSurvey}', [TechnicianPortalController::class, 'showSurvey'])->name('surveys.show');
    Route::post('/surveys/{siteSurvey}/complete', [TechnicianPortalController::class, 'completeSurvey'])->name('surveys.complete');
});

// Customer Self-Service Client Portal
Route::middleware(['auth', 'customer'])->group(function () {
    Route::get('/portal', [CustomerPortalController::class, 'dashboard'])->name('customer.portal');
    Route::prefix('portal')->name('portal.')->group(function () {
        Route::get('/dashboard', [CustomerPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/equipment', [CustomerPortalController::class, 'equipment'])->name('equipment');
    Route::get('/amc', [CustomerPortalController::class, 'amc'])->name('amc');
    Route::post('/amc/renew', [CustomerPortalController::class, 'requestAmcRenewal'])->name('amc.renew');
    Route::get('/tickets', [CustomerPortalController::class, 'tickets'])->name('tickets');
    Route::get('/tickets/create', [CustomerPortalController::class, 'createTicket'])->name('tickets.create');
    Route::post('/tickets', [CustomerPortalController::class, 'storeTicket'])->name('tickets.store');
    Route::get('/tickets/{ticket}', [CustomerPortalController::class, 'showTicket'])->name('tickets.show');
    Route::get('/surveys', [CustomerPortalController::class, 'surveys'])->name('surveys');
    Route::get('/surveys/{siteSurvey}', [CustomerPortalController::class, 'showSurvey'])->name('surveys.show');
    Route::get('/invoices', [CustomerPortalController::class, 'invoices'])->name('invoices');
    Route::get('/quotations', [CustomerPortalController::class, 'quotations'])->name('quotations');
    Route::post('/quotations/{quotation}/accept', [CustomerPortalController::class, 'acceptQuotation'])->name('quotations.accept');
    Route::post('/quotations/{quotation}/reject', [CustomerPortalController::class, 'rejectQuotation'])->name('quotations.reject');
    Route::post('/site-work-request', [CustomerPortalController::class, 'submitSiteWorkRequest'])->name('site-work-request');
    });
});

// Staff & Admin ERP Login Aliases
Route::get('/staff/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])->name('staff.login');
Route::get('/admin/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])->name('admin.login');
Route::get('/staff', fn() => redirect('/login?type=staff'));

require __DIR__ . '/auth.php';