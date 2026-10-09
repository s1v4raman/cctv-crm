<?php

namespace App\Services;

use App\Models\InstallationJob;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class JobCostingService
{
    /**
     * Compute comprehensive financial P&L for a single installation project
     */
    public function calculateJobCosting(InstallationJob $job): array
    {
        $job->loadMissing([
            'quotation.lead',
            'quotation.items.product',
            'assignedTechnician.salaryStructure',
            'invoice.payments',
            'expenseClaims.user',
            'completionReport',
        ]);

        $quotation = $job->quotation;
        $lead = $quotation?->lead;
        $invoice = $job->invoice;
        $technician = $job->assignedTechnician;
        $salary = $technician?->salaryStructure;

        // 1. Revenue Streams
        $subtotal = (float) ($invoice ? $invoice->subtotal : ($quotation?->subtotal ?? 0.0));
        $discount = (float) ($invoice ? $invoice->discount : ($quotation?->discount ?? 0.0));
        $taxableRevenue = max(0.0, $subtotal - $discount);
        $taxAmount = (float) ($invoice ? $invoice->tax_amount : ($quotation?->tax_amount ?? 0.0));
        $grossRevenue = (float) ($invoice ? $invoice->total : ($quotation?->total ?? ($taxableRevenue + $taxAmount)));
        $amountPaid = (float) ($invoice ? $invoice->amount_paid : 0.0);
        $receivableBalance = max(0.0, $grossRevenue - $amountPaid);

        // 2. Hardware Bill of Materials (BOM) & COGS
        $hardwareItems = [];
        $totalHardwareCogs = 0.0;
        $totalHardwareSelling = 0.0;
        $items = $quotation?->items ?? collect();

        foreach ($items as $item) {
            $product = $item->product;
            $qty = (float) ($item->quantity ?: 1.0);
            $sellingPrice = (float) ($item->unit_price ?: 0.0);
            $sellingTotal = (float) ($item->total > 0 ? $item->total : ($qty * $sellingPrice));

            $unitCost = (float) ($product?->cost_price ?? 0.0);
            if ($unitCost <= 0) {
                // If not specified in catalog, estimate cost as 60% of selling price
                $unitCost = round($sellingPrice * 0.60, 2);
            }
            $totalCost = round($qty * $unitCost, 2);
            $itemMargin = $sellingTotal - $totalCost;
            $itemMarginPercent = $sellingTotal > 0 ? round(($itemMargin / $sellingTotal) * 100, 1) : 0.0;

            $totalHardwareCogs += $totalCost;
            $totalHardwareSelling += $sellingTotal;

            $hardwareItems[] = [
                'item_id'        => $item->id,
                'name'           => $product?->name ?: ($item->item_name ?: 'Hardware Item'),
                'sku'            => $product?->sku ?? 'N/A',
                'hsn_code'       => $product?->hsn_code ?? '8525',
                'quantity'       => $qty,
                'unit'           => $product?->unit ?: ($item->unit ?: 'NOS'),
                'unit_cost'      => $unitCost,
                'unit_price'     => $sellingPrice,
                'total_cost'     => $totalCost,
                'total_selling'  => $sellingTotal,
                'item_margin'    => $itemMargin,
                'margin_percent' => $itemMarginPercent,
            ];
        }

        // Fallback for hardware COGS if items were empty
        if ($totalHardwareCogs <= 0 && $taxableRevenue > 0) {
            $totalHardwareCogs = round($taxableRevenue * 0.55, 2);
        }

        // 3. Direct Labor Costs
        $laborHours = (float) ($job->labor_hours_logged ?? 0.0);
        $isHoursEstimated = false;

        if ($laborHours <= 0) {
            $isHoursEstimated = true;
            // Estimate based on hardware camera count (1.5 hrs per camera, minimum 4 hours)
            $cameraCount = count(array_filter($hardwareItems, fn($i) => str_contains(strtolower($i['name']), 'camera') || str_contains(strtolower($i['name']), 'dome') || str_contains(strtolower($i['name']), 'bullet')));
            $laborHours = max(4.0, $cameraCount > 0 ? ($cameraCount * 1.5) : 6.0);
        }

        // Determine Hourly Labor Rate
        $hourlyRate = (float) ($job->custom_hourly_rate ?? 0.0);
        if ($hourlyRate <= 0) {
            if ($salary && $salary->hourly_rate > 0) {
                $hourlyRate = (float) $salary->hourly_rate;
            } elseif ($salary && $salary->daily_rate > 0) {
                $hourlyRate = round((float) $salary->daily_rate / 8.0, 2);
            } elseif ($salary && $salary->base_salary_monthly > 0) {
                $hourlyRate = round((float) $salary->base_salary_monthly / (26.0 * 8.0), 2);
            } else {
                $hourlyRate = 175.00; // Standard baseline field technician hourly rate in INR
            }
        }

        $totalLaborCost = round($laborHours * $hourlyRate, 2);

        // 4. Direct Field Travel & Materials Expense Claims
        $claims = $job->expenseClaims ?? collect();
        $approvedClaims = $claims->whereIn('status', ['approved', 'paid']);
        $totalFieldExpenses = (float) $approvedClaims->sum('amount');

        $expenseList = [];
        foreach ($approvedClaims as $claim) {
            $expenseList[] = [
                'claim_id'    => $claim->id,
                'claim_no'    => $claim->claim_no,
                'date'        => $claim->expense_date?->format('d M Y'),
                'category'    => $claim->category_label,
                'employee'    => $claim->user?->name ?? 'Technician',
                'description' => $claim->description,
                'amount'      => (float) $claim->amount,
            ];
        }

        // 5. Additional Project Overheads
        $otherDirectCosts = (float) ($job->other_direct_costs ?? 0.0);

        // 6. Total Project Cost & Profitability
        $totalProjectCost = round($totalHardwareCogs + $totalLaborCost + $totalFieldExpenses + $otherDirectCosts, 2);
        $netGrossProfit = round($taxableRevenue - $totalProjectCost, 2);
        $grossMarginPercent = $taxableRevenue > 0 ? round(($netGrossProfit / $taxableRevenue) * 100, 1) : 0.0;

        // 7. Margin Health Status
        if ($grossMarginPercent >= 35.0) {
            $marginStatus = 'high_profit';
            $marginLabel = 'High Margin';
            $marginBadgeClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border-emerald-300 dark:border-emerald-800';
        } elseif ($grossMarginPercent >= 20.0) {
            $marginStatus = 'healthy';
            $marginLabel = 'Healthy Margin';
            $marginBadgeClass = 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 border-blue-300 dark:border-blue-800';
        } elseif ($grossMarginPercent >= 5.0) {
            $marginStatus = 'slim_margin';
            $marginLabel = 'Slim Margin';
            $marginBadgeClass = 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border-amber-300 dark:border-amber-800';
        } else {
            $marginStatus = 'loss_making';
            $marginLabel = 'Loss Making';
            $marginBadgeClass = 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border-rose-300 dark:border-rose-800 animate-pulse';
        }

        return [
            'job'                   => $job,
            'job_no'                => $job->job_no,
            'scheduled_date'        => $job->scheduled_date ? Carbon::parse($job->scheduled_date)->format('d M Y') : 'N/A',
            'status'                => $job->status,
            'customer_name'         => $lead?->company_legal_name ?: ($lead?->customer_name ?: 'Client'),
            'customer_phone'        => $lead?->phone ?? 'N/A',
            'site_address'          => $lead?->site_address ?? 'N/A',
            'technician_name'       => $technician?->name ?? 'Unassigned',
            'quotation_no'          => $quotation?->quotation_no ?? 'N/A',
            'invoice_no'            => $invoice?->invoice_no ?? 'Uninvoiced',
            
            // Financial Breakdown
            'revenue_taxable'       => $taxableRevenue,
            'tax_amount'            => $taxAmount,
            'revenue_gross'         => $grossRevenue,
            'amount_collected'      => $amountPaid,
            'receivable_balance'    => $receivableBalance,
            
            // Costs Breakdown
            'hardware_cogs'         => $totalHardwareCogs,
            'hardware_items'        => $hardwareItems,
            'labor_hours'           => $laborHours,
            'is_hours_estimated'    => $isHoursEstimated,
            'hourly_rate'           => $hourlyRate,
            'labor_cost'            => $totalLaborCost,
            'field_expenses'        => $totalFieldExpenses,
            'expense_claims_list'   => $expenseList,
            'other_direct_costs'    => $otherDirectCosts,
            'costing_notes'         => $job->costing_notes,
            'total_cost'            => $totalProjectCost,
            
            // Net Margins
            'gross_profit'          => $netGrossProfit,
            'gross_margin_percent'  => $grossMarginPercent,
            'margin_status'         => $marginStatus,
            'margin_label'          => $marginLabel,
            'margin_badge_class'    => $marginBadgeClass,
        ];
    }

    /**
     * Get aggregated Job Costing & Portfolio Profitability summary
     */
    public function getProjectsCostingSummary(array $filters = [], ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $query = InstallationJob::with([
            'quotation.lead',
            'quotation.items.product',
            'assignedTechnician.salaryStructure',
            'invoice.payments',
            'expenseClaims',
        ])->orderByDesc('created_at');

        if ($startDate && $endDate) {
            $query->whereBetween('scheduled_date', [$startDate->toDateString(), $endDate->toDateString()]);
        }

        if (!empty($filters['technician_id'])) {
            $query->where('assigned_technician_id', $filters['technician_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $query->where(function ($q) use ($s) {
                $q->where('job_no', 'like', "%{$s}%")
                  ->orWhereHas('quotation.lead', function ($l) use ($s) {
                      $l->where('customer_name', 'like', "%{$s}%")
                        ->orWhere('phone', 'like', "%{$s}%")
                        ->orWhere('site_address', 'like', "%{$s}%");
                  });
            });
        }

        $jobs = $query->get();

        $calculatedJobs = [];
        $totalPortfolioRevenue = 0.0;
        $totalPortfolioCogs = 0.0;
        $totalPortfolioLabor = 0.0;
        $totalPortfolioExpenses = 0.0;
        $totalPortfolioOther = 0.0;
        $totalPortfolioProfit = 0.0;
        $highProfitCount = 0;
        $healthyCount = 0;
        $slimCount = 0;
        $lossMakingCount = 0;

        /** @var \App\Models\InstallationJob $job */
        foreach ($jobs as $job) {
            $costing = $this->calculateJobCosting($job);

            // Margin status filter
            if (!empty($filters['margin_status']) && $costing['margin_status'] !== $filters['margin_status']) {
                continue;
            }

            $calculatedJobs[] = $costing;

            $totalPortfolioRevenue  += $costing['revenue_taxable'];
            $totalPortfolioCogs     += $costing['hardware_cogs'];
            $totalPortfolioLabor    += $costing['labor_cost'];
            $totalPortfolioExpenses += $costing['field_expenses'];
            $totalPortfolioOther    += $costing['other_direct_costs'];
            $totalPortfolioProfit   += $costing['gross_profit'];

            match ($costing['margin_status']) {
                'high_profit' => $highProfitCount++,
                'healthy'     => $healthyCount++,
                'slim_margin' => $slimCount++,
                'loss_making' => $lossMakingCount++,
            };
        }

        $avgMarginPercent = $totalPortfolioRevenue > 0 
            ? round(($totalPortfolioProfit / $totalPortfolioRevenue) * 100, 1) 
            : 0.0;

        return [
            'jobs'                     => $calculatedJobs,
            'total_projects_count'     => count($calculatedJobs),
            'total_portfolio_revenue'  => round($totalPortfolioRevenue, 2),
            'total_portfolio_cogs'     => round($totalPortfolioCogs, 2),
            'total_portfolio_labor'    => round($totalPortfolioLabor, 2),
            'total_portfolio_expenses' => round($totalPortfolioExpenses, 2),
            'total_portfolio_other'    => round($totalPortfolioOther, 2),
            'total_portfolio_cost'     => round($totalPortfolioCogs + $totalPortfolioLabor + $totalPortfolioExpenses + $totalPortfolioOther, 2),
            'total_portfolio_profit'   => round($totalPortfolioProfit, 2),
            'avg_margin_percent'       => $avgMarginPercent,
            'high_profit_count'        => $highProfitCount,
            'healthy_count'            => $healthyCount,
            'slim_count'               => $slimCount,
            'loss_making_count'        => $lossMakingCount,
        ];
    }

    /**
     * Generate CSV content for portfolio costing export
     */
    public function exportPortfolioCsv(array $costingSummary): string
    {
        $output = fopen('php://temp', 'r+');

        fputcsv($output, ['--- CCTV PROJECT COSTING & PROFITABILITY LEDGER ---']);
        fputcsv($output, ['Total Projects Analyzed', $costingSummary['total_projects_count']]);
        fputcsv($output, ['Total Taxable Revenue (₹)', number_format($costingSummary['total_portfolio_revenue'], 2, '.', '')]);
        fputcsv($output, ['Total Hardware COGS (₹)', number_format($costingSummary['total_portfolio_cogs'], 2, '.', '')]);
        fputcsv($output, ['Total Labor Cost (₹)', number_format($costingSummary['total_portfolio_labor'], 2, '.', '')]);
        fputcsv($output, ['Total Direct Expenses (₹)', number_format($costingSummary['total_portfolio_expenses'], 2, '.', '')]);
        fputcsv($output, ['Total Net Project Profit (₹)', number_format($costingSummary['total_portfolio_profit'], 2, '.', '')]);
        fputcsv($output, ['Average Portfolio Margin (%)', $costingSummary['avg_margin_percent'] . '%']);
        fputcsv($output, []);

        fputcsv($output, [
            'Job Number',
            'Scheduled Date',
            'Customer / Legal Name',
            'Site Address',
            'Assigned Technician',
            'Status',
            'Taxable Revenue (₹)',
            'Gross Invoiced (₹)',
            'Amount Collected (₹)',
            'Hardware Cost (₹)',
            'Labor Hours',
            'Labor Rate (₹/hr)',
            'Labor Cost (₹)',
            'Field Travel Expenses (₹)',
            'Other Direct Costs (₹)',
            'Total Project Cost (₹)',
            'Net Gross Profit (₹)',
            'Gross Margin (%)',
            'Margin Health Status',
        ]);

        foreach ($costingSummary['jobs'] as $row) {
            fputcsv($output, [
                $row['job_no'],
                $row['scheduled_date'],
                $row['customer_name'],
                $row['site_address'],
                $row['technician_name'],
                ucfirst($row['status']),
                number_format($row['revenue_taxable'], 2, '.', ''),
                number_format($row['revenue_gross'], 2, '.', ''),
                number_format($row['amount_collected'], 2, '.', ''),
                number_format($row['hardware_cogs'], 2, '.', ''),
                $row['labor_hours'],
                number_format($row['hourly_rate'], 2, '.', ''),
                number_format($row['labor_cost'], 2, '.', ''),
                number_format($row['field_expenses'], 2, '.', ''),
                number_format($row['other_direct_costs'], 2, '.', ''),
                number_format($row['total_cost'], 2, '.', ''),
                number_format($row['gross_profit'], 2, '.', ''),
                $row['gross_margin_percent'] . '%',
                $row['margin_label'],
            ]);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }
}
