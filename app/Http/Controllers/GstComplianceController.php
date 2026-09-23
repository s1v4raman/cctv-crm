<?php

namespace App\Http\Controllers;

use App\Models\GatewaySetting;
use App\Models\GstFiling;
use App\Services\GstComplianceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GstComplianceController extends Controller
{
    protected GstComplianceService $gstService;

    public function __construct(GstComplianceService $gstService)
    {
        $this->gstService = $gstService;
    }

    /**
     * Executive GST & Tax Compliance Center Dashboard
     */
    public function index(Request $request): View
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        
        try {
            $parsedDate = Carbon::parse($selectedMonth . '-01');
        } catch (\Throwable $e) {
            $parsedDate = now()->startOfMonth();
            $selectedMonth = $parsedDate->format('Y-m');
        }

        $startDate = $parsedDate->copy()->startOfMonth();
        $endDate   = $parsedDate->copy()->endOfMonth();

        $activeTab = $request->input('tab', 'overview'); // overview, gstr1, itc, gstr3b, hsn, filings, settings

        // Compile GST Data
        $companyGst = $this->gstService->getCompanyGstDetails();
        $gstr1Data  = $this->gstService->getGstr1Data($startDate, $endDate);
        $itcData    = $this->gstService->getItcData($startDate, $endDate);
        $gstr3bData = $this->gstService->getGstr3bSummary($startDate, $endDate);

        // Get or initialize filing record for this month
        $filing = GstFiling::where('period', $selectedMonth)->first();

        // Past filing history
        $filingHistory = GstFiling::with('filer')->orderByDesc('period')->get();

        return view('finance.gst.index', compact(
            'selectedMonth',
            'activeTab',
            'startDate',
            'endDate',
            'companyGst',
            'gstr1Data',
            'itcData',
            'gstr3bData',
            'filing',
            'filingHistory'
        ));
    }

    /**
     * Update Company GST & Legal Business Profile
     */
    public function updateCompanyGst(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_trade_name' => ['required', 'string', 'max:255'],
            'company_legal_name' => ['nullable', 'string', 'max:255'],
            'company_gstin'      => ['required', 'string', 'max:15'],
            'company_pan'        => ['nullable', 'string', 'max:10'],
            'company_state'      => ['required', 'string', 'max:100'],
            'company_state_code' => ['required', 'string', 'max:2'],
            'company_address'    => ['nullable', 'string', 'max:500'],
        ]);

        $settings = GatewaySetting::getSettings();
        $settings->update($validated);

        return back()->with('status', '✅ Company GST profile updated successfully.');
    }

    /**
     * Record GST Return Filing & Payment Challan
     */
    public function recordFiling(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period'           => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'gstr1_status'     => ['required', 'in:pending,reconciled,filed'],
            'gstr3b_status'    => ['required', 'in:pending,reconciled,filed'],
            'tax_paid'         => ['nullable', 'numeric', 'min:0'],
            'challan_no'       => ['nullable', 'string', 'max:50'],
            'cin_number'       => ['nullable', 'string', 'max:50'],
            'filing_date'      => ['nullable', 'date'],
            'payment_mode'     => ['nullable', 'string', 'max:50'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        $periodDate = Carbon::parse($validated['period'] . '-01');
        $startDate = $periodDate->copy()->startOfMonth();
        $endDate   = $periodDate->copy()->endOfMonth();

        // Calculate latest metrics for logging snapshot
        $gstr1Data  = $this->gstService->getGstr1Data($startDate, $endDate);
        $itcData    = $this->gstService->getItcData($startDate, $endDate);
        $gstr3bData = $this->gstService->getGstr3bSummary($startDate, $endDate);

        GstFiling::updateOrCreate(
            ['period' => $validated['period']],
            [
                'gstr1_status'     => $validated['gstr1_status'],
                'gstr3b_status'    => $validated['gstr3b_status'],
                'total_turnover'   => $gstr1Data['total_gross_turnover'],
                'taxable_turnover' => $gstr1Data['total_taxable_turnover'],
                'output_tax'       => $gstr1Data['total_output_tax'],
                'input_tax_credit' => $itcData['grand_total_itc'],
                'net_tax_payable'  => $gstr3bData['net_tax_payable_cash']['total'],
                'tax_paid'         => $validated['tax_paid'] ?? 0.00,
                'challan_no'       => $validated['challan_no'] ?? null,
                'cin_number'       => $validated['cin_number'] ?? null,
                'filing_date'      => $validated['filing_date'] ?? ($validated['gstr3b_status'] === 'filed' ? now()->toDateString() : null),
                'payment_mode'     => $validated['payment_mode'] ?? 'online_portal',
                'notes'            => $validated['notes'] ?? null,
                'filed_by'         => Auth::id(),
            ]
        );

        return back()->with('status', "🎉 GST Return status for {$validated['period']} saved successfully.");
    }

    /**
     * Download GST Portal GSTR-1 JSON Schema Payload
     */
    public function exportJson(Request $request): Response
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $parsedDate = Carbon::parse($selectedMonth . '-01');
        $startDate = $parsedDate->copy()->startOfMonth();
        $endDate   = $parsedDate->copy()->endOfMonth();

        $jsonData = $this->gstService->exportGstr1Json($startDate, $endDate);
        $jsonString = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $filename = "GSTR1_{$selectedMonth}_{$jsonData['gstin']}.json";

        return response($jsonString, 200, [
            'Content-Type'        => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Download GSTR-1 Sales Report CSV
     */
    public function exportGstr1Csv(Request $request): Response
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $parsedDate = Carbon::parse($selectedMonth . '-01');
        $startDate = $parsedDate->copy()->startOfMonth();
        $endDate   = $parsedDate->copy()->endOfMonth();

        $csv = $this->gstService->exportGstr1Csv($startDate, $endDate);
        $filename = "GSTR1_Sales_Report_{$selectedMonth}.csv";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Download GSTR-2B / ITC Purchase Ledger CSV
     */
    public function exportItcCsv(Request $request): Response
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $parsedDate = Carbon::parse($selectedMonth . '-01');
        $startDate = $parsedDate->copy()->startOfMonth();
        $endDate   = $parsedDate->copy()->endOfMonth();

        $csv = $this->gstService->exportItcCsv($startDate, $endDate);
        $filename = "GSTR2B_ITC_Purchases_Report_{$selectedMonth}.csv";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Download Comprehensive GST Audit & Tax Filing PDF Package
     */
    public function exportPdf(Request $request)
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        $parsedDate = Carbon::parse($selectedMonth . '-01');
        $startDate = $parsedDate->copy()->startOfMonth();
        $endDate   = $parsedDate->copy()->endOfMonth();

        $companyGst = $this->gstService->getCompanyGstDetails();
        $gstr1Data  = $this->gstService->getGstr1Data($startDate, $endDate);
        $itcData    = $this->gstService->getItcData($startDate, $endDate);
        $gstr3bData = $this->gstService->getGstr3bSummary($startDate, $endDate);
        $filing     = GstFiling::where('period', $selectedMonth)->first();

        $pdf = Pdf::loadView('finance.gst.report_pdf', compact(
            'selectedMonth',
            'startDate',
            'endDate',
            'companyGst',
            'gstr1Data',
            'itcData',
            'gstr3bData',
            'filing'
        ));

        $filename = "GST_Tax_Compliance_Report_{$selectedMonth}.pdf";
        return $pdf->download($filename);
    }
}
