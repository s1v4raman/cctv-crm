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
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\ExpenseClaimController;
use App\Http\Controllers\GstComplianceController;
use App\Http\Controllers\JobCostingController;
use App\Http\Controllers\AccountsReceivableController;
use App\Http\Controllers\AccountsPayableController;
use App\Http\Controllers\PettyCashController;
use App\Http\Controllers\ModuleHubController;

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

    // ================================================================
    // ADMIN ONLY â€” Full Read + Write + Delete for all modules
    // Staff & Technician are blocked from all routes in this group.
    // ================================================================
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

        // Products Management (Admin Only)
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        // Inventory Stock Adjustments
        Route::post('/inventory/{product}/adjust', [InventoryController::class, 'adjustStock'])->name('inventory.adjust');

        // Installed Equipment Delete
        Route::delete('/equipment/{equipment}', [InstalledEquipmentController::class, 'destroy'])->name('equipment.destroy');

        // User Accounts CRUD
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        });

        // Suppliers & Vendors Write
        Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

        // Purchase Orders Write
        Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
        Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        Route::get('/purchase-orders/{purchase_order}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-orders.edit');
        Route::put('/purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'update'])->name('purchase-orders.update');
        Route::delete('/purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'destroy'])->name('purchase-orders.destroy');
        Route::post('/purchase-orders/{purchase_order}/receive', [PurchaseOrderController::class, 'receiveItems'])->name('purchase-orders.receive');
        Route::patch('/purchase-orders/{purchase_order}/status', [PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.updateStatus');
        Route::patch('/purchase-orders/{purchase_order}/payment', [PurchaseOrderController::class, 'updatePayment'])->name('purchase-orders.updatePayment');
        Route::patch('/purchase-orders/{purchase_order}/payment', [PurchaseOrderController::class, 'updatePayment'])->name('purchase-orders.updatePayment');

        // Automated Alerts & Notification Engine (Admin Only)
        Route::get('/alerts', [AlertNotificationController::class, 'index'])->name('alerts.index');
        Route::get('/alerts/templates', [AlertNotificationController::class, 'templates'])->name('alerts.templates');
        Route::get('/alerts/gateways', [AlertNotificationController::class, 'gateways'])->name('alerts.gateways');
        Route::put('/alerts/templates/{template}', [AlertNotificationController::class, 'updateTemplate'])->name('alerts.templates.update');
        Route::put('/alerts/gateways', [AlertNotificationController::class, 'updateGateways'])->name('alerts.gateways.update');
        Route::post('/alerts/gateways/test-sms', [AlertNotificationController::class, 'testSmsGateway'])->name('alerts.gateways.test-sms');
        Route::post('/alerts/gateways/test-otp', [AlertNotificationController::class, 'testOtp'])->name('alerts.gateways.test-otp');
        Route::post('/alerts/gateways/test-email', [AlertNotificationController::class, 'testEmailGateway'])->name('alerts.gateways.test-email');
        Route::post('/alerts/broadcast', [AlertNotificationController::class, 'sendManualBroadcast'])->name('alerts.broadcast');
        Route::post('/alerts/run-sweep', [AlertNotificationController::class, 'runAutomatedSweep'])->name('alerts.run-sweep');

        // Attendance Admin Write (managing other employees' records)
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::post('/attendance/quick-mark', [AttendanceController::class, 'quickMark'])->name('attendance.quick-mark');
        Route::post('/attendance/batch-store', [AttendanceController::class, 'batchStore'])->name('attendance.batch-store');
        Route::put('/attendance/{attendance}', [AttendanceController::class, 'update'])->name('attendance.update');

        // Leave Requests Admin Approvals (Admin Only)
        Route::patch('/attendance/leaves/{leave}/approve', [LeaveRequestController::class, 'approve'])->name('leaves.approve');
        Route::patch('/attendance/leaves/{leave}/reject', [LeaveRequestController::class, 'reject'])->name('leaves.reject');

        // Finance & Salary (Admin Only)
        Route::get('/finance/salaries', [FinanceController::class, 'salaries'])->name('finance.salaries.index');
        Route::post('/finance/salaries/{user}', [FinanceController::class, 'updateSalary'])->name('finance.salaries.update');
        Route::get('/finance/payroll', [FinanceController::class, 'payrollIndex'])->name('finance.payroll.index');
        Route::post('/finance/payroll/generate', [FinanceController::class, 'generatePayroll'])->name('finance.payroll.generate');
        Route::get('/finance/payroll/{payroll}', [FinanceController::class, 'showPayroll'])->name('finance.payroll.show');
        Route::get('/finance/payroll/{payroll}/pdf', [FinanceController::class, 'downloadPayslipPdf'])->name('finance.payroll.pdf');
        Route::get('/finance/payroll/{payroll}/view-pdf', [FinanceController::class, 'streamPayslipPdf'])->name('finance.payroll.viewPdf');
        Route::match(['get', 'post'], '/finance/payroll/{payroll}/send-whatsapp', [FinanceController::class, 'sendWhatsApp'])->name('finance.payroll.sendWhatsApp');
        Route::post('/finance/payroll/{payroll}/status', [FinanceController::class, 'updatePayrollStatus'])->name('finance.payroll.updateStatus');
        Route::get('/finance/analytics', [FinanceController::class, 'analytics'])->name('finance.analytics');
        
        // Expense & Travel Claims Admin Approvals & Payouts (Admin Only)
        Route::patch('/finance/expenses/{claim}/approve', [ExpenseClaimController::class, 'approve'])->name('finance.expenses.approve');
        Route::patch('/finance/expenses/{claim}/reject', [ExpenseClaimController::class, 'reject'])->name('finance.expenses.reject');
        Route::post('/finance/expenses/{claim}/pay', [ExpenseClaimController::class, 'markPaid'])->name('finance.expenses.pay');

        // GST & Tax Compliance Center (Admin Only)
        Route::get('/finance/gst', [GstComplianceController::class, 'index'])->name('finance.gst.index');
        Route::post('/finance/gst/settings', [GstComplianceController::class, 'updateCompanyGst'])->name('finance.gst.settings.update');
        Route::post('/finance/gst/filing', [GstComplianceController::class, 'recordFiling'])->name('finance.gst.filing.record');
        Route::get('/finance/gst/export-json', [GstComplianceController::class, 'exportJson'])->name('finance.gst.export-json');
        Route::get('/finance/gst/export-csv', [GstComplianceController::class, 'exportGstr1Csv'])->name('finance.gst.export-csv');
        Route::get('/finance/gst/export-itc-csv', [GstComplianceController::class, 'exportItcCsv'])->name('finance.gst.export-itc-csv');
        Route::get('/finance/gst/export-pdf', [GstComplianceController::class, 'exportPdf'])->name('finance.gst.export-pdf');

        // Job Costing & Per-Project Profit & Loss (Admin Only)
        Route::get('/finance/job-costing', [JobCostingController::class, 'index'])->name('finance.job-costing.index');
        Route::get('/finance/job-costing/export-csv', [JobCostingController::class, 'exportPortfolioCsv'])->name('finance.job-costing.export-csv');
        Route::get('/finance/job-costing/{job}', [JobCostingController::class, 'show'])->name('finance.job-costing.show');
        Route::post('/finance/job-costing/{job}', [JobCostingController::class, 'updateCosting'])->name('finance.job-costing.update');
        Route::get('/finance/job-costing/{job}/pdf', [JobCostingController::class, 'exportPdf'])->name('finance.job-costing.pdf');

        // Accounts Receivable & Debtors Aging (Admin Only)
        Route::get('/finance/receivables', [AccountsReceivableController::class, 'index'])->name('finance.receivables.index');
        Route::get('/finance/receivables/export-csv', [AccountsReceivableController::class, 'exportAgingCsv'])->name('finance.receivables.export-csv');
        Route::get('/finance/receivables/export-pdf', [AccountsReceivableController::class, 'exportAgingPdf'])->name('finance.receivables.export-pdf');
        Route::post('/finance/receivables/sweep', [AccountsReceivableController::class, 'runBulkSweep'])->name('finance.receivables.sweep');
        Route::post('/finance/receivables/reminder/{invoice}', [AccountsReceivableController::class, 'sendReminder'])->name('finance.receivables.reminder');
        Route::get('/finance/receivables/customer/{lead}', [AccountsReceivableController::class, 'customerLedger'])->name('finance.receivables.customer');
        Route::get('/finance/receivables/customer/{lead}/pdf', [AccountsReceivableController::class, 'customerStatementPdf'])->name('finance.receivables.customer.pdf');

        // Vendor Accounts Payable & 3-Way Matching (Admin Only)
        Route::get('/finance/payables', [AccountsPayableController::class, 'index'])->name('finance.payables.index');
        Route::post('/finance/payables/payment', [AccountsPayableController::class, 'recordPayment'])->name('finance.payables.payment');
        Route::get('/finance/payables/export-csv', [AccountsPayableController::class, 'exportApCsv'])->name('finance.payables.export-csv');
        Route::get('/finance/payables/export-three-way-csv', [AccountsPayableController::class, 'exportThreeWayMatchCsv'])->name('finance.payables.export-three-way-csv');
        Route::get('/finance/payables/export-pdf', [AccountsPayableController::class, 'exportApPdf'])->name('finance.payables.export-pdf');
        Route::get('/finance/payables/supplier/{supplier}', [AccountsPayableController::class, 'supplierLedger'])->name('finance.payables.supplier');
        Route::get('/finance/payables/supplier/{supplier}/pdf', [AccountsPayableController::class, 'supplierStatementPdf'])->name('finance.payables.supplier.pdf');

        // Petty Cash & Daily Field Cash Reconciliation (Admin Only)
        Route::get('/finance/petty-cash', [PettyCashController::class, 'index'])->name('finance.petty_cash.index');
        Route::post('/finance/petty-cash/advance', [PettyCashController::class, 'issueAdvance'])->name('finance.petty_cash.advance');
        Route::post('/finance/petty-cash/collection', [PettyCashController::class, 'recordCollection'])->name('finance.petty_cash.collection');
        Route::post('/finance/petty-cash/expense', [PettyCashController::class, 'recordExpense'])->name('finance.petty_cash.expense');
        Route::post('/finance/petty-cash/handover', [PettyCashController::class, 'recordHandover'])->name('finance.petty_cash.handover');
        Route::get('/finance/petty-cash/reconcile/{account}', [PettyCashController::class, 'showReconcile'])->name('finance.petty_cash.reconcile');
        Route::post('/finance/petty-cash/reconcile/{account}', [PettyCashController::class, 'storeReconcile'])->name('finance.petty_cash.reconcile.store');
        Route::get('/finance/petty-cash/ledger/{account}', [PettyCashController::class, 'accountLedger'])->name('finance.petty_cash.ledger');
        Route::get('/finance/petty-cash/voucher/{transaction}/pdf', [PettyCashController::class, 'voucherPdf'])->name('finance.petty_cash.voucher.pdf');
        Route::get('/finance/petty-cash/export-pdf', [PettyCashController::class, 'exportPdf'])->name('finance.petty_cash.export-pdf');
        Route::get('/finance/petty-cash/export-csv', [PettyCashController::class, 'exportCsv'])->name('finance.petty_cash.export-csv');

        // Executive Business Analytics (Admin Only)
        Route::get('/analytics', [ExecutiveAnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/analytics/api', [ExecutiveAnalyticsController::class, 'apiData'])->name('analytics.api');
        Route::get('/analytics/export-pdf', [ExecutiveAnalyticsController::class, 'exportPdf'])->name('analytics.export-pdf');
        Route::get('/analytics/cost-profit', [ExecutiveAnalyticsController::class, 'costProfit'])->name('analytics.cost-profit');
        Route::get('/analytics/cost-profit/export-pdf', [ExecutiveAnalyticsController::class, 'exportCostProfitPdf'])->name('analytics.cost-profit.export-pdf');

        // Technician Performance Dashboard (Admin Only)
        Route::get('/analytics/technicians', [ExecutiveAnalyticsController::class, 'technicians'])->name('analytics.technicians');
        Route::get('/analytics/technicians/export-pdf', [ExecutiveAnalyticsController::class, 'exportTechniciansPdf'])->name('analytics.technicians.export-pdf');
        Route::get('/analytics/api/technicians', [ExecutiveAnalyticsController::class, 'apiTechnicians'])->name('analytics.api.technicians');

        // Monthly Recurring Revenue & AMC Retention Report (Admin Only)
        Route::get('/analytics/mrr-retention', [ExecutiveAnalyticsController::class, 'mrrRetention'])->name('analytics.mrr-retention');
        Route::get('/analytics/mrr-retention/export-pdf', [ExecutiveAnalyticsController::class, 'exportMrrRetentionPdf'])->name('analytics.mrr-retention.export-pdf');
        Route::get('/analytics/api/mrr-retention', [ExecutiveAnalyticsController::class, 'apiMrrRetention'])->name('analytics.api.mrr-retention');
    });

    // ================================================================
    // ALL INTERNAL USERS (Admin + Staff + Technician) — Read-Only Views
    // All write actions & executive analytics are gated by admin middleware.
    // ================================================================
    Route::middleware(['internal'])->group(function () {

        // Dashboard & Category Hubs
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
        Route::get('/employee-workforce', [ModuleHubController::class, 'employeeHub'])->name('employee.hub');
        Route::get('/finance-accounting', [ModuleHubController::class, 'financeHub'])->name('finance.hub');

        // Operations Calendar & Dispatch
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
        Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');

        // Leads Read-Only
        Route::get('/leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
        Route::get('/leads/{lead}/vcard', [LeadController::class, 'downloadVcard'])->name('leads.vcard');

        // Quotations Operations
        Route::get('/leads/{lead}/quote', [QuotationController::class, 'create'])->name('quotations.create');
        Route::post('/leads/{lead}/quote', [QuotationController::class, 'store'])->name('quotations.store');
        Route::get('/quotations/create', [QuotationController::class, 'createGeneral'])->name('quotations.create-general');
        Route::get('/quotations/{quotation}/edit', [QuotationController::class, 'edit'])->name('quotations.edit');
        Route::put('/quotations/{quotation}', [QuotationController::class, 'update'])->name('quotations.update');
        Route::delete('/quotations/{quotation}', [QuotationController::class, 'destroy'])->name('quotations.destroy');
        Route::post('/quotations/{quotation}/accept', [QuotationController::class, 'markAccepted'])->name('quotations.accept');
        Route::post('/quotations/{quotation}/reject', [QuotationController::class, 'markRejected'])->name('quotations.reject');
        Route::patch('/quotations/{quotation}/sent', [QuotationController::class, 'markSent'])->name('quotations.markSent');
        Route::post('/quotations/{quotation}/send-email', [QuotationController::class, 'sendEmail'])->name('quotations.send-email');
        Route::delete('/quotation-items/{item}', [QuotationController::class, 'destroyItem'])->name('quotation-items.destroy');

        // Quotations Read-Only
        Route::get('/quotations', [QuotationController::class, 'index'])->name('quotations.index');
        Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
        Route::get('/quotations/{quotation}/send-whatsapp', [QuotationController::class, 'sendWhatsApp'])->name('quotations.send-whatsapp');
        Route::get('/quotations/{quotation}/pdf/{format}', [QuotationController::class, 'downloadPdf'])->name('quotations.pdf');
        Route::get('/quotations/{quotation}/vcard', [QuotationController::class, 'downloadVcard'])->name('quotations.vcard');

        // CCTV Storage & Cable Estimator (View & Convert)
        Route::get('/estimator', [CctvEstimatorController::class, 'index'])->name('estimator.index');
        Route::post('/estimator/convert-quotation', [CctvEstimatorController::class, 'convertToQuotation'])->name('estimator.convert');

        // Invoices & Payments Write
        Route::get('/jobs/{job}/invoice/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('/jobs/{job}/invoice', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::post('/invoices/{invoice}/payments', [InvoiceController::class, 'storePayment'])->name('payments.store');
        Route::delete('/payments/{payment}', [InvoiceController::class, 'destroyPayment'])->name('payments.destroy');

        // AMC Contracts Write
        Route::get('/amcs/create', [AmcContractController::class, 'create'])->name('amcs.create');
        Route::post('/amcs', [AmcContractController::class, 'store'])->name('amcs.store');
        Route::patch('/amcs/{amc}/status', [AmcContractController::class, 'updateStatus'])->name('amcs.updateStatus');
        Route::delete('/amcs/{amc}', [AmcContractController::class, 'destroy'])->name('amcs.destroy');
        Route::post('/amc-visits/{visit}/assign', [AmcContractController::class, 'assignVisitTechnician'])->name('amc-visits.assign');
        Route::post('/amc-visits/{visit}/complete', [AmcContractController::class, 'completeVisit'])->name('amc-visits.complete');

        // Site Surveys Write
        Route::get('/site-surveys/create', [SiteSurveyController::class, 'create'])->name('site-surveys.create');
        Route::post('/site-surveys', [SiteSurveyController::class, 'store'])->name('site-surveys.store');
        Route::get('/site-surveys/{site_survey}/edit', [SiteSurveyController::class, 'edit'])->name('site-surveys.edit');
        Route::put('/site-surveys/{site_survey}', [SiteSurveyController::class, 'update'])->name('site-surveys.update');
        Route::delete('/site-surveys/{site_survey}', [SiteSurveyController::class, 'destroy'])->name('site-surveys.destroy');
        Route::delete('/site-surveys/photos/{photo}', [SiteSurveyController::class, 'destroyPhoto'])->name('site-surveys.photos.destroy');

        // Service Tickets Write
        Route::get('/service-tickets/create', [ServiceTicketController::class, 'create'])->name('service-tickets.create');
        Route::post('/service-tickets', [ServiceTicketController::class, 'store'])->name('service-tickets.store');
        Route::get('/service-tickets/{service_ticket}/edit', [ServiceTicketController::class, 'edit'])->name('service-tickets.edit');
        Route::put('/service-tickets/{service_ticket}', [ServiceTicketController::class, 'update'])->name('service-tickets.update');
        Route::delete('/service-tickets/{service_ticket}', [ServiceTicketController::class, 'destroy'])->name('service-tickets.destroy');
        Route::patch('/service-tickets/{service_ticket}/status', [ServiceTicketController::class, 'updateStatus'])->name('service-tickets.updateStatus');
        Route::post('/service-tickets/{service_ticket}/assign', [ServiceTicketController::class, 'assignTechnician'])->name('service-tickets.assign');
        Route::post('/service-tickets/{service_ticket}/accept', [ServiceTicketController::class, 'acceptRequest'])->name('service-tickets.accept');
        Route::post('/service-tickets/{service_ticket}/reject', [ServiceTicketController::class, 'rejectRequest'])->name('service-tickets.reject');
        Route::post('/service-tickets/simulate-email', [InboundEmailSupportController::class, 'simulate'])->name('service-tickets.simulate-email');

        // RMA & Warranty Claims Write
        Route::get('/rma/create', [RmaClaimController::class, 'create'])->name('rma.create');
        Route::post('/rma', [RmaClaimController::class, 'store'])->name('rma.store');
        Route::get('/rma/{rma}/edit', [RmaClaimController::class, 'edit'])->name('rma.edit');
        Route::put('/rma/{rma}', [RmaClaimController::class, 'update'])->name('rma.update');
        Route::delete('/rma/{rma}', [RmaClaimController::class, 'destroy'])->name('rma.destroy');
        Route::post('/rma/{rma}/dispatch', [RmaClaimController::class, 'dispatchToVendor'])->name('rma.dispatch');
        Route::post('/rma/{rma}/vendor-status', [RmaClaimController::class, 'updateVendorStatus'])->name('rma.vendor-status');
        Route::post('/rma/{rma}/resolution', [RmaClaimController::class, 'recordResolution'])->name('rma.resolution');

        // Jobs Read-Only
        Route::get('/jobs', [InstallationJobController::class, 'index'])->name('jobs.index');
        Route::get('/jobs/{job}', [InstallationJobController::class, 'show'])->name('jobs.show');

        // Products Autocomplete Search for Quotations (Admin & Staff)
        Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');

        // Inventory Management Read-Only (Stock Overview & Audit Log)
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::get('/inventory/movements', [InventoryController::class, 'movements'])->name('inventory.movements');

        // Installed Equipment & Warranty API
        Route::get('/api/equipment/lookup-serial', [\App\Http\Controllers\Api\EquipmentLookupController::class, 'lookup'])->name('equipment.lookup');

        // RMA & Warranty Claims Read-Only
        Route::get('/rma', [RmaClaimController::class, 'index'])->name('rma.index');
        Route::get('/rma/{rma}', [RmaClaimController::class, 'show'])->name('rma.show');
        Route::get('/rma/{rma}/dispatch-pdf', [RmaClaimController::class, 'downloadDispatchChallan'])->name('rma.dispatch-pdf');

        // Suppliers & Vendors Read-Only
        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');

        // Purchase Orders Read-Only
        Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
        Route::get('/purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');

        // Invoices Read-Only
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');

        // AMC Contracts Read-Only
        Route::get('/amcs', [AmcContractController::class, 'index'])->name('amcs.index');
        Route::get('/amcs/{amc}', [AmcContractController::class, 'show'])->name('amcs.show');

        // Site Surveys Read-Only
        Route::get('/site-surveys', [SiteSurveyController::class, 'index'])->name('site-surveys.index');
        Route::get('/site-surveys/{site_survey}', [SiteSurveyController::class, 'show'])->name('site-surveys.show');

        // Service Tickets Read-Only
        Route::get('/service-tickets/export', [ServiceTicketController::class, 'exportCsv'])->name('service-tickets.export');
        Route::get('/service-tickets/export-pdf', [ServiceTicketController::class, 'exportPdf'])->name('service-tickets.export-pdf');
        Route::get('/service-tickets/lead-amc/{lead}', [ServiceTicketController::class, 'getLeadAmc'])->name('service-tickets.lead-amc');
        Route::get('/service-tickets', [ServiceTicketController::class, 'index'])->name('service-tickets.index');
        Route::get('/service-tickets/{service_ticket}', [ServiceTicketController::class, 'show'])->name('service-tickets.show');

        // Digital Job Completion Reports Read-Only
        Route::get('/jcr', [JobCompletionReportController::class, 'index'])->name('jcr.index');

        // Employee Attendance — Read-Only view (all internal users can see the dashboard)
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

        // Employee Attendance — Self-Service Clock In/Out (all employees can punch their own record)
        Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
        Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');

        // Employee Leave Management & Requests (All internal workforce)
        Route::get('/attendance/leaves', [LeaveRequestController::class, 'index'])->name('leaves.index');
        Route::post('/attendance/leaves', [LeaveRequestController::class, 'store'])->name('leaves.store');
        Route::delete('/attendance/leaves/{leave}/cancel', [LeaveRequestController::class, 'cancel'])->name('leaves.cancel');

        // Expense & Travel Claims (All internal workforce: Staff & Technicians)
        Route::get('/finance/expenses', [ExpenseClaimController::class, 'index'])->name('finance.expenses.index');
        Route::post('/finance/expenses', [ExpenseClaimController::class, 'store'])->name('finance.expenses.store');
        Route::delete('/finance/expenses/{claim}/cancel', [ExpenseClaimController::class, 'cancel'])->name('finance.expenses.cancel');

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

    // Installed Equipment & Hardware Registry (Technicians + Internal Staff â€” except destroy)
    Route::resource('equipment', InstalledEquipmentController::class)->except(['destroy']);

    // Barcode & Serial Number Instant Lookup API
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
Route::post('/staff/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store'])->name('staff.login.post');
Route::get('/admin/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])->name('admin.login');
Route::get('/customer/login', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])->name('customer.login');
Route::get('/staff', fn() => redirect('/staff/login'));

require __DIR__ . '/auth.php';
