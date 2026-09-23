<?php

namespace App\Http\Controllers;

use App\Models\InstallationJob;
use App\Models\User;
use App\Services\JobCostingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class JobCostingController extends Controller
{
    protected JobCostingService $costingService;

    public function __construct(JobCostingService $costingService)
    {
        $this->costingService = $costingService;
    }

    /**
     * Job Costing & Portfolio Profitability Dashboard
     */
    public function index(Request $request): View
    {
        $range = $request->input('range', 'all');
        $startDate = null;
        $endDate = null;

        if ($range === 'this_month') {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        } elseif ($range === 'last_month') {
            $startDate = now()->subMonth()->startOfMonth();
            $endDate = now()->subMonth()->endOfMonth();
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'));
            $endDate = Carbon::parse($request->input('end_date'));
        }

        $filters = [
            'search'        => $request->input('search'),
            'technician_id' => $request->input('technician_id'),
            'status'        => $request->input('status'),
            'margin_status' => $request->input('margin_status'),
        ];

        $summary = $this->costingService->getProjectsCostingSummary($filters, $startDate, $endDate);
        $technicians = User::where('role', 'technician')->orderBy('name')->get();

        return view('finance.job_costing.index', compact(
            'summary',
            'technicians',
            'filters',
            'range',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Deep-Dive Single Project P&L Breakdown
     */
    public function show(InstallationJob $job): View
    {
        $costing = $this->costingService->calculateJobCosting($job);

        return view('finance.job_costing.show', compact('costing', 'job'));
    }

    /**
     * Update project labor hours, contractor rate, and other overhead costs
     */
    public function updateCosting(Request $request, InstallationJob $job): RedirectResponse
    {
        $validated = $request->validate([
            'labor_hours_logged' => ['nullable', 'numeric', 'min:0'],
            'custom_hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'other_direct_costs' => ['nullable', 'numeric', 'min:0'],
            'costing_notes'      => ['nullable', 'string', 'max:1000'],
        ]);

        $job->update($validated);

        return back()->with('status', "✅ Cost sheet updated for Project #{$job->job_no}. Net Margin recalculated.");
    }

    /**
     * Download Official Project P&L Statement PDF
     */
    public function exportPdf(InstallationJob $job)
    {
        $costing = $this->costingService->calculateJobCosting($job);
        $pdf = Pdf::loadView('finance.job_costing.job_pnl_pdf', compact('costing', 'job'));
        $filename = "Project_PNL_Statement_{$job->job_no}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Export Full Project Portfolio Profitability CSV
     */
    public function exportPortfolioCsv(Request $request): Response
    {
        $range = $request->input('range', 'all');
        $startDate = null;
        $endDate = null;

        if ($range === 'this_month') {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        } elseif ($range === 'last_month') {
            $startDate = now()->subMonth()->startOfMonth();
            $endDate = now()->subMonth()->endOfMonth();
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->input('start_date'));
            $endDate = Carbon::parse($request->input('end_date'));
        }

        $filters = [
            'search'        => $request->input('search'),
            'technician_id' => $request->input('technician_id'),
            'status'        => $request->input('status'),
            'margin_status' => $request->input('margin_status'),
        ];

        $summary = $this->costingService->getProjectsCostingSummary($filters, $startDate, $endDate);
        $csv = $this->costingService->exportPortfolioCsv($summary);
        $filename = "Project_Portfolio_Costing_Report_" . now()->format('Y_m_d') . ".csv";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
