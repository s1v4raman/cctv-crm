<?php

namespace App\Http\Controllers;

use App\Services\ExecutiveAnalyticsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExecutiveAnalyticsController extends Controller
{
    public function index(Request $request, ExecutiveAnalyticsService $analyticsService): View
    {
        $range = $request->input('range', 'this_month');
        [$startDate, $endDate] = $this->resolveDateRange($range, $request->input('start_date'), $request->input('end_date'));

        $overview = $analyticsService->getExecutiveOverview($startDate, $endDate);
        $selectedRange = $range;

        return view('analytics.index', compact('overview', 'selectedRange', 'startDate', 'endDate'));
    }

    public function apiData(Request $request, ExecutiveAnalyticsService $analyticsService): JsonResponse
    {
        $range = $request->input('range', 'this_month');
        [$startDate, $endDate] = $this->resolveDateRange($range, $request->input('start_date'), $request->input('end_date'));

        $overview = $analyticsService->getExecutiveOverview($startDate, $endDate);

        return response()->json($overview);
    }

    public function exportPdf(Request $request, ExecutiveAnalyticsService $analyticsService)
    {
        $range = $request->input('range', 'this_month');
        [$startDate, $endDate] = $this->resolveDateRange($range, $request->input('start_date'), $request->input('end_date'));

        $overview = $analyticsService->getExecutiveOverview($startDate, $endDate);

        $pdf = Pdf::loadView('analytics.executive_report_pdf', compact('overview', 'startDate', 'endDate'));

        $filename = "Executive_Financial_Performance_Report_" . $startDate->format('Y_m_d') . ".pdf";
        return $pdf->download($filename);
    }

    /**
     * Technician Performance Dashboard (FTFR, MTTR, Completed Installations).
     */
    public function technicians(Request $request, ExecutiveAnalyticsService $analyticsService): View
    {
        $range = $request->input('range', 'this_month');
        [$startDate, $endDate] = $this->resolveDateRange($range, $request->input('start_date'), $request->input('end_date'));

        $data = $analyticsService->getTechnicianPerformanceDetails($startDate, $endDate);
        $selectedRange = $range;

        return view('analytics.technicians', compact('data', 'selectedRange', 'startDate', 'endDate'));
    }

    /**
     * Monthly Recurring Revenue & AMC Retention Report.
     */
    public function mrrRetention(Request $request, ExecutiveAnalyticsService $analyticsService): View
    {
        $range = $request->input('range', 'this_month');
        [$startDate, $endDate] = $this->resolveDateRange($range, $request->input('start_date'), $request->input('end_date'));

        $data = $analyticsService->getMrrAndRetentionMetrics($startDate, $endDate);
        $selectedRange = $range;

        return view('analytics.mrr_retention', compact('data', 'selectedRange', 'startDate', 'endDate'));
    }

    /**
     * Export Technician Performance PDF.
     */
    public function exportTechniciansPdf(Request $request, ExecutiveAnalyticsService $analyticsService)
    {
        $range = $request->input('range', 'this_month');
        [$startDate, $endDate] = $this->resolveDateRange($range, $request->input('start_date'), $request->input('end_date'));

        $data = $analyticsService->getTechnicianPerformanceDetails($startDate, $endDate);

        $pdf = Pdf::loadView('analytics.technician_report_pdf', compact('data', 'startDate', 'endDate'));

        $filename = "Technician_Performance_Scorecard_" . $startDate->format('Y_m_d') . ".pdf";
        return $pdf->download($filename);
    }

    /**
     * Export MRR & AMC Retention PDF.
     */
    public function exportMrrRetentionPdf(Request $request, ExecutiveAnalyticsService $analyticsService)
    {
        $range = $request->input('range', 'this_month');
        [$startDate, $endDate] = $this->resolveDateRange($range, $request->input('start_date'), $request->input('end_date'));

        $data = $analyticsService->getMrrAndRetentionMetrics($startDate, $endDate);

        $pdf = Pdf::loadView('analytics.mrr_retention_pdf', compact('data', 'startDate', 'endDate'));

        $filename = "MRR_and_AMC_Retention_Report_" . $startDate->format('Y_m_d') . ".pdf";
        return $pdf->download($filename);
    }

    /**
     * API endpoint for Technician Performance Data.
     */
    public function apiTechnicians(Request $request, ExecutiveAnalyticsService $analyticsService): JsonResponse
    {
        $range = $request->input('range', 'this_month');
        [$startDate, $endDate] = $this->resolveDateRange($range, $request->input('start_date'), $request->input('end_date'));

        $data = $analyticsService->getTechnicianPerformanceDetails($startDate, $endDate);

        return response()->json($data);
    }

    /**
     * API endpoint for MRR & AMC Retention Data.
     */
    public function apiMrrRetention(Request $request, ExecutiveAnalyticsService $analyticsService): JsonResponse
    {
        $range = $request->input('range', 'this_month');
        [$startDate, $endDate] = $this->resolveDateRange($range, $request->input('start_date'), $request->input('end_date'));

        $data = $analyticsService->getMrrAndRetentionMetrics($startDate, $endDate);

        return response()->json($data);
    }

    /**
     * Dedicated Cost & Pre-Tax Profit Analysis (Minus Employee Salaries, Product Buy Cost, Taxes).
     */
    public function costProfit(Request $request, ExecutiveAnalyticsService $analyticsService): View
    {
        $range = $request->input('range', 'this_month');
        [$startDate, $endDate] = $this->resolveDateRange($range, $request->input('start_date'), $request->input('end_date'));

        $data = $analyticsService->getCostProfitAnalysis($startDate, $endDate);
        $selectedRange = $range;

        return view('analytics.cost_profit', compact('data', 'selectedRange', 'startDate', 'endDate'));
    }

    /**
     * Export Cost & Profit Analysis PDF.
     */
    public function exportCostProfitPdf(Request $request, ExecutiveAnalyticsService $analyticsService)
    {
        $range = $request->input('range', 'this_month');
        [$startDate, $endDate] = $this->resolveDateRange($range, $request->input('start_date'), $request->input('end_date'));

        $data = $analyticsService->getCostProfitAnalysis($startDate, $endDate);

        $pdf = Pdf::loadView('analytics.cost_profit_pdf', compact('data', 'startDate', 'endDate'));

        $filename = "Cost_and_Net_Profit_Analysis_" . $startDate->format('Y_m_d') . ".pdf";
        return $pdf->download($filename);
    }

    private function resolveDateRange(string $range, ?string $customStart, ?string $customEnd): array
    {
        $now = Carbon::now();

        return match ($range) {
            'today'        => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'last_month'   => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            'this_quarter' => [$now->copy()->firstOfQuarter(), $now->copy()->lastOfQuarter()],
            'this_year'    => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'all_time'     => [Carbon::create(2024, 1, 1), $now->copy()->endOfDay()],
            'custom'       => [
                $customStart ? Carbon::parse($customStart)->startOfDay() : $now->copy()->startOfMonth(),
                $customEnd ? Carbon::parse($customEnd)->endOfDay() : $now->copy()->endOfMonth(),
            ],
            default        => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()], // this_month
        };
    }
}
