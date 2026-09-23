<?php

namespace App\Services;

use App\Models\AmcContract;
use App\Models\AmcVisit;
use App\Models\EmployeeSalary;
use App\Models\InstallationJob;
use App\Models\Invoice;
use App\Models\JobCompletionReport;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\RmaClaim;
use App\Models\ServiceTicket;
use App\Models\SiteSurvey;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExecutiveAnalyticsService
{
    /**
     * Get complete executive overview for a specified date range.
     */
    public function getExecutiveOverview(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfMonth();

        return [
            'date_range'        => [
                'start' => $startDate->format('Y-m-d'),
                'end'   => $endDate->format('Y-m-d'),
                'label' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'),
            ],
            'financials'        => $this->getFinancialMetrics($startDate, $endDate),
            'cost_profit'       => $this->getCostProfitAnalysis($startDate, $endDate),
            'amc'               => $this->getAmcMetrics($startDate, $endDate),
            'revenue_streams'   => $this->getRevenueStreamsBreakdown($startDate, $endDate),
            'monthly_trend'     => $this->getMonthlyFinancialsTrend(12),
            'lead_funnel'       => $this->getLeadFunnelMetrics($startDate, $endDate),
            'technicians'       => $this->getTechnicianScorecard($startDate, $endDate),
            'top_products'      => $this->getTopProductsByMargin(5),
            'rma_summary'       => $this->getRmaSummary($startDate, $endDate),
        ];
    }

    /**
     * Financial profitability, gross margin, cashflow, without-tax profit, salary deduction & receivables aging.
     */
    public function getFinancialMetrics(Carbon $startDate, Carbon $endDate): array
    {
        // 1. Invoices Revenue Breakdown (With Tax vs Without Tax)
        $invoices = Invoice::whereBetween('invoice_date', [$startDate, $endDate])->get();
        $invoicedRevenueWithTax = (float) $invoices->sum('total');
        $invoicedRevenueWithoutTax = (float) $invoices->sum('subtotal');
        $taxCollectedSales = (float) $invoices->sum('tax_amount');

        // Fallback if subtotal was 0 in any legacy seed data
        if ($invoicedRevenueWithoutTax <= 0 && $invoicedRevenueWithTax > 0) {
            $invoicedRevenueWithoutTax = round($invoicedRevenueWithTax / 1.18, 2);
            $taxCollectedSales = round($invoicedRevenueWithTax - $invoicedRevenueWithoutTax, 2);
        }

        // 2. Payments Realized (Cash Inflow)
        $cashCollected = (float) Payment::whereBetween('paid_on', [$startDate, $endDate])->sum('amount');

        // 3. Product Buy Cost (Hardware COGS from Quotation Items for invoiced jobs)
        $quotationIds = $invoices->whereNotNull('quotation_id')->pluck('quotation_id');

        $cogs = (float) QuotationItem::whereIn('quotation_id', $quotationIds)
            ->join('products', 'quotation_items.product_id', '=', 'products.id')
            ->selectRaw('SUM(quotation_items.quantity * products.cost_price) as total_cogs')
            ->value('total_cogs') ?? 0.0;

        // If no products joined, estimate hardware cost as 55% of without-tax revenue
        if ($cogs <= 0 && $invoicedRevenueWithoutTax > 0) {
            $cogs = round($invoicedRevenueWithoutTax * 0.55, 2);
        }

        // 4. Gross Profit Without Tax = Invoiced Revenue Without Tax - Product Buy Cost
        $grossProfitWithoutTax = max(0, $invoicedRevenueWithoutTax - $cogs);
        $grossMarginPercent = $invoicedRevenueWithoutTax > 0 
            ? round(($grossProfitWithoutTax / $invoicedRevenueWithoutTax) * 100, 1) 
            : 0.0;

        // 5. Automatically Calculate Employee Salary Expenses for Period
        $salaryData = $this->calculateEmployeeSalariesForPeriod($startDate, $endDate);
        $totalEmployeeSalary = $salaryData['total_salary'];
        $salaryCreditedCost = $salaryData['salary_credited_cost'];

        // 6. Procurement & Tax Paid on Purchase Orders
        $procurementData = $this->getProcurementAndTaxMetrics($startDate, $endDate);
        $taxPaidCost = $procurementData['po_tax_paid_cost'];
        $poProductBuyCost = $procurementData['po_product_buy_cost'];

        // 7. NET OPERATING PROFIT WITHOUT TAX = Gross Profit Without Tax - Employee Salaries
        $netProfitWithoutTax = round($grossProfitWithoutTax - $totalEmployeeSalary, 2);
        $netMarginPercent = $invoicedRevenueWithoutTax > 0 
            ? round(($netProfitWithoutTax / $invoicedRevenueWithoutTax) * 100, 1) 
            : 0.0;

        // 8. Total Outstanding Receivables (All unpaid/partially paid invoices)
        $allInvoices = Invoice::with('payments')->whereIn('status', ['unpaid', 'partially_paid', 'overdue'])->get();
        $totalReceivables = 0.0;
        $agingBuckets = [
            'current' => 0.0,
            '1_30'    => 0.0,
            '31_60'   => 0.0,
            '60_plus' => 0.0,
        ];

        $today = Carbon::today();
        foreach ($allInvoices as $inv) {
            $due = $inv->balanceDue();
            $totalReceivables += $due;

            if (!$inv->due_date || $inv->due_date->isFuture() || $inv->due_date->isToday()) {
                $agingBuckets['current'] += $due;
            } else {
                $daysOverdue = (int) $inv->due_date->diffInDays($today);
                if ($daysOverdue <= 30) {
                    $agingBuckets['1_30'] += $due;
                } elseif ($daysOverdue <= 60) {
                    $agingBuckets['31_60'] += $due;
                } else {
                    $agingBuckets['60_plus'] += $due;
                }
            }
        }

        $legacyGrossProfit = max(0, $invoicedRevenueWithTax - $cogs);
        $legacyGrossMargin = $invoicedRevenueWithTax > 0 ? round(($legacyGrossProfit / $invoicedRevenueWithTax) * 100, 1) : 0.0;

        return [
            // Backward-compatible keys
            'invoiced_revenue'          => $invoicedRevenueWithTax,
            'cogs'                      => $cogs,
            'gross_profit'              => $legacyGrossProfit,
            'gross_margin_percent'      => $legacyGrossMargin,
            'cash_collected'            => $cashCollected,
            'total_receivables'         => $totalReceivables,
            'aging_buckets'             => $agingBuckets,

            // Core Pre-Tax & Cost-Profit Analysis Metrics
            'revenue_with_tax'          => $invoicedRevenueWithTax,
            'revenue_without_tax'       => $invoicedRevenueWithoutTax,
            'tax_collected_cost'        => $taxCollectedSales,
            'product_buy_cost'          => $cogs,
            'gross_profit_without_tax'  => $grossProfitWithoutTax,
            'gross_margin_without_tax_percent' => $grossMarginPercent,
            'salary_credited_cost'      => $salaryCreditedCost,
            'employee_salary_cost'      => $totalEmployeeSalary,
            'net_profit_without_tax'    => $netProfitWithoutTax,
            'net_margin_percent'        => $netMarginPercent,
            'tax_paid_cost'             => $taxPaidCost,
            'po_product_buy_cost'       => $poProductBuyCost,
            'po_total_procurement_cost' => $procurementData['po_total_procurement_cost'],
            'salary_breakdown'          => $salaryData['role_breakdown'],
            'salary_details'            => $salaryData['details'],
        ];
    }

    /**
     * Recurring AMC revenue & service delivery.
     */
    public function getAmcMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $activeContracts = AmcContract::where('status', 'active')->count();
        $annualRecurringRevenue = (float) AmcContract::where('status', 'active')->sum('value');
        $monthlyRecurringRevenue = round($annualRecurringRevenue / 12, 2);

        $visitsScheduled = AmcVisit::whereBetween('scheduled_date', [$startDate, $endDate])->count();
        $visitsCompleted = AmcVisit::whereBetween('scheduled_date', [$startDate, $endDate])
            ->where('status', 'completed')->count();
        $deliveryRate = $visitsScheduled > 0 ? round(($visitsCompleted / $visitsScheduled) * 100, 1) : 100.0;

        return [
            'active_contracts' => $activeContracts,
            'arr'              => $annualRecurringRevenue,
            'mrr'              => $monthlyRecurringRevenue,
            'visits_scheduled' => $visitsScheduled,
            'visits_completed' => $visitsCompleted,
            'delivery_rate'    => $deliveryRate,
        ];
    }

    /**
     * Split revenue across Project Installations, AMC Subscriptions, and Service Tickets.
     */
    public function getRevenueStreamsBreakdown(Carbon $startDate, Carbon $endDate): array
    {
        // 1. Projects / Installations (Invoices linked to InstallationJob)
        $projectRevenue = (float) Invoice::whereNotNull('installation_job_id')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('total');

        // 2. AMC Contracts signed in range
        $amcRevenue = (float) AmcContract::whereBetween('start_date', [$startDate, $endDate])
            ->sum('value');

        // 3. Service Ticket Billable Invoices (or estimated 10% of invoices if not separately tagged)
        $serviceRevenue = (float) Invoice::whereNull('installation_job_id')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('total');

        $totalStreams = $projectRevenue + $amcRevenue + $serviceRevenue;

        return [
            'projects' => $projectRevenue,
            'amc'      => $amcRevenue,
            'service'  => $serviceRevenue,
            'total'    => $totalStreams,
            'percentages' => [
                'projects' => $totalStreams > 0 ? round(($projectRevenue / $totalStreams) * 100, 1) : 0,
                'amc'      => $totalStreams > 0 ? round(($amcRevenue / $totalStreams) * 100, 1) : 0,
                'service'  => $totalStreams > 0 ? round(($serviceRevenue / $totalStreams) * 100, 1) : 0,
            ]
        ];
    }

    /**
     * 12-Month Financial Performance & Profitability Trend.
     */
    public function getMonthlyFinancialsTrend(int $monthsCount = 12): array
    {
        $months = collect(range($monthsCount - 1, 0))
            ->map(fn($offset) => Carbon::now()->subMonths($offset)->startOfMonth());

        $labels = [];
        $revenue = [];
        $revenueWithoutTax = [];
        $cogs = [];
        $salary = [];
        $profit = [];
        $collections = [];

        foreach ($months as $month) {
            $mStart = $month->copy()->startOfMonth();
            $mEnd = $month->copy()->endOfMonth();

            $labels[] = $month->format('M Y');

            $mRev = (float) Invoice::whereBetween('invoice_date', [$mStart, $mEnd])->sum('total');
            $mSubtotal = (float) Invoice::whereBetween('invoice_date', [$mStart, $mEnd])->sum('subtotal');
            if ($mSubtotal <= 0 && $mRev > 0) {
                $mSubtotal = round($mRev / 1.18, 2);
            }

            $mCash = (float) Payment::whereBetween('paid_on', [$mStart, $mEnd])->sum('amount');
            $mCost = $mSubtotal > 0 ? round($mSubtotal * 0.55, 2) : 0.0;
            
            // Auto calculate salary for that month
            $mSalary = $this->calculateEmployeeSalariesForPeriod($mStart, $mEnd)['total_salary'];
            $mNetProfit = round($mSubtotal - $mCost - $mSalary, 2);

            $revenue[] = $mRev;
            $revenueWithoutTax[] = $mSubtotal;
            $cogs[] = $mCost;
            $salary[] = $mSalary;
            $profit[] = $mNetProfit;
            $collections[] = $mCash;
        }

        return [
            'labels'                 => $labels,
            'revenue'                => $revenue,
            'revenue_without_tax'    => $revenueWithoutTax,
            'cogs'                   => $cogs,
            'product_buy_cost'       => $cogs,
            'salary'                 => $salary,
            'salary_cost'            => $salary,
            'profit'                 => $profit,
            'net_profit_without_tax' => $profit,
            'collections'            => $collections,
        ];
    }

    /**
     * Automatically Calculate Employee Salaries for any given timeframe.
     */
    public function calculateEmployeeSalariesForPeriod(Carbon $startDate, Carbon $endDate): array
    {
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();
        $daysCount = max(1, $start->diffInDays($end) + 1);

        $employees = User::whereIn('role', ['admin', 'staff', 'technician'])
            ->with(['salaryStructure'])
            ->orderBy('name')
            ->get();

        // Check if there are generated and paid payrolls in this period
        $payrollsInPeriod = Payroll::whereBetween('period_start', [$start->toDateString(), $end->toDateString()])
            ->orWhereBetween('period_end', [$start->toDateString(), $end->toDateString()])
            ->get();

        $paidPayrolls = $payrollsInPeriod->where('status', 'paid');
        $salaryCreditedCost = (float) $paidPayrolls->sum('net_salary');

        $totalEstimatedSalary = 0.0;
        $roleBreakdown = [
            'admin'      => ['count' => 0, 'salary' => 0.0, 'credited' => 0.0],
            'staff'      => ['count' => 0, 'salary' => 0.0, 'credited' => 0.0],
            'technician' => ['count' => 0, 'salary' => 0.0, 'credited' => 0.0],
        ];

        $employeeDetails = [];

        foreach ($employees as $emp) {
            $role = in_array($emp->role, ['admin', 'staff', 'technician']) ? $emp->role : 'staff';
            $roleBreakdown[$role]['count']++;

            $empPayroll = $payrollsInPeriod->firstWhere('user_id', $emp->id);
            $isCredited = $empPayroll && $empPayroll->status === 'paid';
            $creditedAmount = $isCredited ? (float) $empPayroll->net_salary : 0.0;
            $roleBreakdown[$role]['credited'] += $creditedAmount;

            $salaryStruct = $emp->salaryStructure;
            $computedCost = 0.0;

            if ($empPayroll) {
                // If a payroll slip exists for this period, use its net salary
                $computedCost = (float) $empPayroll->net_salary;
            } elseif ($salaryStruct && (float) $salaryStruct->base_salary_monthly > 0) {
                $monthly = (float) $salaryStruct->base_salary_monthly;
                $daily = (float) ($salaryStruct->daily_rate > 0 ? $salaryStruct->daily_rate : round($monthly / 26, 2));

                if ($daysCount >= 28 && $daysCount <= 31) {
                    // Full calendar month
                    $computedCost = $monthly + (float) $salaryStruct->travel_allowance + (float) $salaryStruct->special_allowance - (float) $salaryStruct->deductions;
                } else {
                    // Pro-rated by working days in period (capped at 26 days/month)
                    $computedCost = round($daily * min($daysCount, 26), 2);
                }
            } else {
                // If salary not explicitly configured, fallback to standard baseline role wage
                $defaultMonthly = match($emp->role) {
                    'admin'      => 35000.0,
                    'staff'      => 22000.0,
                    'technician' => 18000.0,
                    default      => 20000.0
                };
                $daily = round($defaultMonthly / 26, 2);
                $computedCost = ($daysCount >= 28 && $daysCount <= 31) ? $defaultMonthly : round($daily * min($daysCount, 26), 2);
            }

            $totalEstimatedSalary += $computedCost;
            $roleBreakdown[$role]['salary'] += $computedCost;

            $employeeDetails[] = [
                'user_id'         => $emp->id,
                'name'            => $emp->name,
                'role'            => $emp->role,
                'has_structure'   => $salaryStruct && $salaryStruct->base_salary_monthly > 0,
                'monthly_base'    => (float) ($salaryStruct?->base_salary_monthly ?? 0),
                'computed_salary' => $computedCost,
                'credited_salary' => $creditedAmount,
                'is_credited'     => $isCredited,
            ];
        }

        // If credited payrolls exist and exceed estimated, take max so expenses are never understated
        $effectiveSalaryCost = max($totalEstimatedSalary, $salaryCreditedCost);

        return [
            'total_salary'          => round($effectiveSalaryCost, 2),
            'salary_credited_cost'  => round($salaryCreditedCost, 2),
            'estimated_salary_cost' => round($totalEstimatedSalary, 2),
            'employee_count'        => $employees->count(),
            'role_breakdown'        => $roleBreakdown,
            'details'               => $employeeDetails,
            'days_in_period'        => $daysCount,
        ];
    }

    /**
     * Get Procurement & Tax Reconciliations (Product Buy Cost on POs and Taxes).
     */
    public function getProcurementAndTaxMetrics(Carbon $startDate, Carbon $endDate): array
    {
        // 1. Purchase Orders (Procurement / Buying hardware from suppliers)
        $pos = PurchaseOrder::whereBetween('order_date', [$startDate, $endDate])->get();
        $poProductBuyCost = (float) $pos->sum('subtotal');
        $poTaxPaidCost    = (float) $pos->sum('tax_amount');
        $poShippingCost   = (float) $pos->sum('shipping_cost');
        $poTotalCost      = (float) $pos->sum('total');
        $poAmountPaid     = (float) $pos->sum('amount_paid');

        // 2. Sales Invoices (Tax billed to customers)
        $invoices = Invoice::whereBetween('invoice_date', [$startDate, $endDate])->get();
        $salesTaxBilled   = (float) $invoices->sum('tax_amount');
        $salesSubtotal    = (float) $invoices->sum('subtotal');
        $salesTotal       = (float) $invoices->sum('total');

        if ($salesSubtotal <= 0 && $salesTotal > 0) {
            $salesSubtotal = round($salesTotal / 1.18, 2);
            $salesTaxBilled = round($salesTotal - $salesSubtotal, 2);
        }

        // 3. Invoiced Jobs COGS (Hardware purchase cost of items sold)
        $quotationIds = $invoices->whereNotNull('quotation_id')->pluck('quotation_id');
        $cogs = (float) QuotationItem::whereIn('quotation_id', $quotationIds)
            ->join('products', 'quotation_items.product_id', '=', 'products.id')
            ->selectRaw('SUM(quotation_items.quantity * products.cost_price) as total_cogs')
            ->value('total_cogs') ?? 0.0;

        if ($cogs <= 0 && $salesSubtotal > 0) {
            $cogs = round($salesSubtotal * 0.55, 2);
        }

        return [
            'product_buy_cost'          => $cogs,
            'po_product_buy_cost'       => $poProductBuyCost,
            'po_tax_paid_cost'          => $poTaxPaidCost,
            'po_total_procurement_cost' => $poTotalCost,
            'po_amount_paid'            => $poAmountPaid,
            'sales_tax_billed'          => $salesTaxBilled,
            'sales_subtotal'            => $salesSubtotal,
            'sales_total'               => $salesTotal,
            'net_tax_liability'         => max(0, $salesTaxBilled - $poTaxPaidCost),
        ];
    }

    /**
     * Dedicated Deep-Dive Cost & Profit Analysis Ledger.
     */
    public function getCostProfitAnalysis(Carbon $startDate, Carbon $endDate): array
    {
        $financials = $this->getFinancialMetrics($startDate, $endDate);
        $salaryData = $this->calculateEmployeeSalariesForPeriod($startDate, $endDate);
        $procurement = $this->getProcurementAndTaxMetrics($startDate, $endDate);

        // Recent Invoices in Range with Profit breakdown per invoice
        $invoices = Invoice::with(['quotation.items.product', 'quotation.lead', 'installationJob'])
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->orderByDesc('invoice_date')
            ->take(15)
            ->get()
            ->map(function ($inv) {
                $subtotal = (float) ($inv->subtotal > 0 ? $inv->subtotal : round($inv->total / 1.18, 2));
                $cogs = 0.0;
                if ($inv->quotation && $inv->quotation->items) {
                    foreach ($inv->quotation->items as $item) {
                        $costPrice = (float) ($item->product?->cost_price ?? ($item->unit_price * 0.55));
                        $cogs += (float) $item->quantity * $costPrice;
                    }
                }
                if ($cogs <= 0 && $subtotal > 0) {
                    $cogs = round($subtotal * 0.55, 2);
                }
                $profitWithoutTax = max(0, $subtotal - $cogs);
                $marginPercent = $subtotal > 0 ? round(($profitWithoutTax / $subtotal) * 100, 1) : 0;

                return [
                    'id'                 => $inv->id,
                    'invoice_no'         => $inv->invoice_no,
                    'client_name'        => $inv->quotation?->lead?->customer_name ?? 'Client',
                    'date'               => $inv->invoice_date->format('d M Y'),
                    'subtotal'           => $subtotal,
                    'tax_amount'         => (float) $inv->tax_amount,
                    'total'              => (float) $inv->total,
                    'product_buy_cost'   => round($cogs, 2),
                    'profit_without_tax' => round($profitWithoutTax, 2),
                    'margin_percent'     => $marginPercent,
                    'status'             => $inv->status,
                ];
            });

        // Purchase Orders in range
        $purchaseOrders = PurchaseOrder::with('supplier')
            ->whereBetween('order_date', [$startDate, $endDate])
            ->orderByDesc('order_date')
            ->take(10)
            ->get();

        return [
            'date_range'      => [
                'start' => $startDate->format('Y-m-d'),
                'end'   => $endDate->format('Y-m-d'),
                'label' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'),
            ],
            'financials'      => $financials,
            'salary'          => $salaryData,
            'procurement'     => $procurement,
            'recent_invoices' => $invoices,
            'purchase_orders' => $purchaseOrders,
        ];
    }

    /**
     * Sales & Lead Funnel Pipeline.
     */
    public function getLeadFunnelMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $totalLeads = Lead::whereBetween('created_at', [$startDate, $endDate])->count();
        $surveys = SiteSurvey::whereBetween('created_at', [$startDate, $endDate])->count();
        $quotations = Quotation::whereBetween('created_at', [$startDate, $endDate])->count();
        $wonProjects = Lead::where('status', 'won')->whereBetween('updated_at', [$startDate, $endDate])->count();

        $conversionRate = $totalLeads > 0 ? round(($wonProjects / $totalLeads) * 100, 1) : 0.0;

        return [
            'total_leads'     => $totalLeads,
            'surveys'         => $surveys,
            'quotations'      => $quotations,
            'won_projects'    => $wonProjects,
            'conversion_rate' => $conversionRate,
        ];
    }

    /**
     * Field Engineering & Technician Performance Leaderboard.
     */
    public function getTechnicianScorecard(Carbon $startDate, Carbon $endDate): array
    {
        $technicians = User::whereIn('role', ['technician', 'admin'])
            ->orderBy('name')
            ->get();

        $scorecard = [];

        foreach ($technicians as $tech) {
            $jobsCompleted = InstallationJob::where('assigned_technician_id', $tech->id)
                ->where('status', 'completed')
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->count();

            $ticketsResolved = ServiceTicket::where('assigned_technician_id', $tech->id)
                ->where('status', 'resolved')
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->count();

            $jcrReports = JobCompletionReport::where('technician_id', $tech->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $avgRating = $jcrReports->count() > 0 ? round($jcrReports->avg('customer_rating'), 1) : 5.0;

            $scorecard[] = [
                'technician_id'     => $tech->id,
                'name'              => $tech->name,
                'role'              => $tech->role,
                'jobs_completed'    => $jobsCompleted,
                'tickets_resolved'  => $ticketsResolved,
                'total_tasks'       => $jobsCompleted + $ticketsResolved,
                'jcr_signoffs'      => $jcrReports->count(),
                'average_rating'    => $avgRating,
            ];
        }

        usort($scorecard, fn($a, $b) => $b['total_tasks'] <=> $a['total_tasks']);

        return $scorecard;
    }

    /**
     * Top selling products & gross margin contribution.
     */
    public function getTopProductsByMargin(int $limit = 5): array
    {
        $products = Product::where('is_active', true)
            ->where('unit_price', '>', 0)
            ->get();

        $ranked = $products->map(function ($prod) {
            $cost = (float) $prod->cost_price;
            $price = (float) $prod->unit_price;
            $marginAmt = max(0, $price - $cost);
            $marginPct = $price > 0 ? round(($marginAmt / $price) * 100, 1) : 0;

            return [
                'id'            => $prod->id,
                'name'          => $prod->name,
                'model_no'      => $prod->model_no ?: 'N/A',
                'unit_price'    => $price,
                'cost_price'    => $cost,
                'margin_amount' => $marginAmt,
                'margin_percent'=> $marginPct,
                'stock'         => $prod->stock_quantity,
            ];
        })->sortByDesc('margin_percent')->values()->take($limit)->all();

        return $ranked;
    }

    /**
     * RMA & Hardware defect metrics.
     */
    public function getRmaSummary(Carbon $startDate, Carbon $endDate): array
    {
        $totalClaims = RmaClaim::whereBetween('created_at', [$startDate, $endDate])->count();
        $replaced = RmaClaim::where('status', 'replaced')->whereBetween('updated_at', [$startDate, $endDate])->count();
        $repaired = RmaClaim::where('status', 'repaired')->whereBetween('updated_at', [$startDate, $endDate])->count();
        $inProcess = RmaClaim::whereIn('status', ['draft', 'shipped_to_vendor', 'in_vendor_repair'])->count();

        return [
            'total_claims' => $totalClaims,
            'replaced'     => $replaced,
            'repaired'     => $repaired,
            'in_process'   => $inProcess,
        ];
    }

    /**
     * Detailed Technician Performance Dashboard metrics.
     * Computes First-Time Fix Rate (FTFR), Mean Time to Resolution (MTTR),
     * and completed installation counts per engineer.
     */
    public function getTechnicianPerformanceDetails(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfMonth();

        $technicians = User::whereIn('role', ['technician', 'admin'])
            ->orderBy('name')
            ->get();

        $technicianData = [];
        $totalResolvedTeam = 0;
        $totalFirstTimeFixTeam = 0;
        $totalResolutionHoursTeam = 0;
        $totalJobsCompletedTeam = 0;
        $totalRatingsTeam = [];

        foreach ($technicians as $tech) {
            // 1. Completed Installation Jobs
            $completedJobs = InstallationJob::where('assigned_technician_id', $tech->id)
                ->where('status', 'completed')
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->count();

            $totalAssignedJobs = InstallationJob::where('assigned_technician_id', $tech->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            // 2. Service Tickets Resolved in range
            $resolvedTickets = ServiceTicket::where('assigned_technician_id', $tech->id)
                ->where('status', 'resolved')
                ->whereBetween('updated_at', [$startDate, $endDate])
                ->get();

            $resolvedCount = $resolvedTickets->count();

            $totalAssignedTickets = ServiceTicket::where('assigned_technician_id', $tech->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            $openBacklog = ServiceTicket::where('assigned_technician_id', $tech->id)
                ->whereIn('status', ['open', 'assigned', 'in_progress'])
                ->count();

            // 3. First-Time Fix Rate (FTFR) Calculation
            // A ticket is considered a First-Time Fix if no subsequent ticket was raised
            // for the same lead_id within 30 days of this ticket's resolution.
            $firstTimeFixCount = 0;
            $totalResolutionHours = 0;

            foreach ($resolvedTickets as $ticket) {
                $resolvedTime = $ticket->resolved_at ?? $ticket->updated_at;
                $thirtyDaysLater = $resolvedTime->copy()->addDays(30);

                $repeatTicketExists = ServiceTicket::where('id', '!=', $ticket->id)
                    ->where('lead_id', $ticket->lead_id)
                    ->whereBetween('created_at', [$resolvedTime, $thirtyDaysLater])
                    ->exists();

                if (!$repeatTicketExists) {
                    $firstTimeFixCount++;
                }

                // 4. Resolution Time in Hours (MTTR)
                $startTime = $ticket->scheduled_date ? Carbon::parse($ticket->scheduled_date)->startOfDay() : $ticket->created_at;
                $hoursTaken = max(0.5, round($startTime->floatDiffInHours($resolvedTime), 1));
                $totalResolutionHours += $hoursTaken;
            }

            $ftfr = $resolvedCount > 0 ? round(($firstTimeFixCount / $resolvedCount) * 100, 1) : 100.0;
            $avgResolutionHours = $resolvedCount > 0 ? round($totalResolutionHours / $resolvedCount, 1) : 0.0;

            // Formatted resolution time
            $avgResolutionFormatted = $avgResolutionHours < 24
                ? "{$avgResolutionHours} hrs"
                : round($avgResolutionHours / 24, 1) . ' days';

            // 5. Customer CSAT & JCR signoffs
            $jcrReports = JobCompletionReport::where('technician_id', $tech->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $avgRating = $jcrReports->count() > 0 ? round($jcrReports->avg('customer_rating'), 1) : 5.0;
            if ($jcrReports->count() > 0) {
                $totalRatingsTeam[] = $avgRating;
            }

            // Accumulate team totals
            $totalResolvedTeam += $resolvedCount;
            $totalFirstTimeFixTeam += $firstTimeFixCount;
            $totalResolutionHoursTeam += $totalResolutionHours;
            $totalJobsCompletedTeam += $completedJobs;

            $technicianData[] = [
                'technician_id'           => $tech->id,
                'name'                    => $tech->name,
                'email'                   => $tech->email,
                'role'                    => $tech->role,
                'jobs_completed'          => $completedJobs,
                'jobs_total'              => $totalAssignedJobs,
                'tickets_resolved'        => $resolvedCount,
                'tickets_total'           => $totalAssignedTickets,
                'first_time_fix_count'    => $firstTimeFixCount,
                'first_time_fix_rate'     => $ftfr,
                'avg_resolution_hours'    => $avgResolutionHours,
                'avg_resolution_formatted'=> $avgResolutionFormatted,
                'open_backlog'            => $openBacklog,
                'jcr_signoffs'            => $jcrReports->count(),
                'average_rating'          => $avgRating,
                'total_activity_score'    => ($completedJobs * 2) + $resolvedCount,
            ];
        }

        // Sort by total activity score descending
        usort($technicianData, fn($a, $b) => $b['total_activity_score'] <=> $a['total_activity_score']);

        // Team aggregate metrics
        $teamAvgFtfr = $totalResolvedTeam > 0
            ? round(($totalFirstTimeFixTeam / $totalResolvedTeam) * 100, 1)
            : 100.0;

        $teamAvgResolutionHours = $totalResolvedTeam > 0
            ? round($totalResolutionHoursTeam / $totalResolvedTeam, 1)
            : 0.0;

        $teamAvgResolutionFormatted = $teamAvgResolutionHours < 24
            ? "{$teamAvgResolutionHours} hrs"
            : round($teamAvgResolutionHours / 24, 1) . ' days';

        $teamAvgRating = !empty($totalRatingsTeam)
            ? round(array_sum($totalRatingsTeam) / count($totalRatingsTeam), 1)
            : 5.0;

        // Chart.js format arrays
        $chartLabels = array_column($technicianData, 'name');
        $chartFtfr = array_column($technicianData, 'first_time_fix_rate');
        $chartMttr = array_column($technicianData, 'avg_resolution_hours');
        $chartJobs = array_column($technicianData, 'jobs_completed');
        $chartTickets = array_column($technicianData, 'tickets_resolved');

        return [
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end'   => $endDate->format('Y-m-d'),
                'label' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'),
            ],
            'technicians' => $technicianData,
            'summary'     => [
                'team_avg_ftfr'              => $teamAvgFtfr,
                'team_avg_resolution_hours'  => $teamAvgResolutionHours,
                'team_avg_resolution_format' => $teamAvgResolutionFormatted,
                'total_jobs_completed'       => $totalJobsCompletedTeam,
                'total_tickets_resolved'     => $totalResolvedTeam,
                'team_avg_csat'              => $teamAvgRating,
                'total_engineers'            => count($technicianData),
            ],
            'chart_data'  => [
                'labels'   => $chartLabels,
                'ftfr'     => $chartFtfr,
                'mttr'     => $chartMttr,
                'jobs'     => $chartJobs,
                'tickets'  => $chartTickets,
            ]
        ];
    }

    /**
     * Monthly Recurring Revenue (MRR) & AMC Retention Report metrics.
     * Computes 12-month MRR trends, contract retention & churn rates,
     * and conversion rate from Leads to Accepted Quotations.
     */
    public function getMrrAndRetentionMetrics(?Carbon $startDate = null, ?Carbon $endDate = null, int $monthsCount = 12): array
    {
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfMonth();

        // 1. Current AMC Portfolio Snapshot
        $activeAmcs = AmcContract::where('status', 'active')->get();
        $totalActiveContracts = $activeAmcs->count();

        $activeArr = 0.0;
        $activeMrr = 0.0;
        $frequencyCounts = ['monthly' => 0, 'quarterly' => 0, 'semi_annually' => 0, 'annually' => 0];
        $frequencyMrr = ['monthly' => 0.0, 'quarterly' => 0.0, 'semi_annually' => 0.0, 'annually' => 0.0];

        foreach ($activeAmcs as $amc) {
            $val = (float) $amc->value;
            $freq = $amc->frequency ?: 'annually';

            $monthlyVal = match ($freq) {
                'monthly' => $val,
                'quarterly' => round($val / 3, 2),
                'semi_annually' => round($val / 6, 2),
                'annually' => round($val / 12, 2),
                default => round($val / 12, 2),
            };

            $annualVal = $monthlyVal * 12;
            $activeArr += $annualVal;
            $activeMrr += $monthlyVal;

            if (isset($frequencyCounts[$freq])) {
                $frequencyCounts[$freq]++;
                $frequencyMrr[$freq] += $monthlyVal;
            } else {
                $frequencyCounts['annually']++;
                $frequencyMrr['annually'] += $monthlyVal;
            }
        }

        $avgContractValue = $totalActiveContracts > 0 ? round($activeArr / $totalActiveContracts, 2) : 0.0;

        // 2. Retention & Churn Rates
        $expiredContracts = AmcContract::where('status', 'expired')->count();
        $cancelledContracts = AmcContract::where('status', 'cancelled')->count();
        $totalHistoricalContracts = $totalActiveContracts + $expiredContracts + $cancelledContracts;

        $retentionRate = ($totalActiveContracts + $expiredContracts) > 0
            ? round(($totalActiveContracts / ($totalActiveContracts + $expiredContracts)) * 100, 1)
            : 100.0;

        $churnRate = round(max(0, 100 - $retentionRate), 1);

        // 3. 12-Month MRR & ARR Timeline Trend
        $months = collect(range($monthsCount - 1, 0))
            ->map(fn($offset) => Carbon::now()->subMonths($offset)->startOfMonth());

        $mrrTimelineLabels = [];
        $mrrValues = [];
        $arrValues = [];
        $newAmcCounts = [];
        $renewedAmcCounts = [];

        foreach ($months as $month) {
            $mStart = $month->copy()->startOfMonth();
            $mEnd = $month->copy()->endOfMonth();

            $mrrTimelineLabels[] = $month->format('M Y');

            // Active contracts in that month
            $contractsInMonth = AmcContract::where('start_date', '<=', $mEnd)
                ->where(function ($q) use ($mStart) {
                    $q->whereNull('end_date')->orWhere('end_date', '>=', $mStart);
                })
                ->where('status', '!=', 'cancelled')
                ->get();

            $monthMrr = 0.0;
            foreach ($contractsInMonth as $c) {
                $v = (float) $c->value;
                $f = $c->frequency ?: 'annually';
                $mVal = match ($f) {
                    'monthly' => $v,
                    'quarterly' => $v / 3,
                    'semi_annually' => $v / 6,
                    'annually' => $v / 12,
                    default => $v / 12,
                };
                $monthMrr += $mVal;
            }

            $mrrValues[] = round($monthMrr, 2);
            $arrValues[] = round($monthMrr * 12, 2);

            // New contracts created in that month
            $newAmcCounts[] = AmcContract::whereBetween('created_at', [$mStart, $mEnd])->count();
            $renewedAmcCounts[] = AmcContract::whereBetween('start_date', [$mStart, $mEnd])
                ->where('status', 'active')
                ->count();
        }

        // 4. 12-Month Lead -> Quotation -> Accepted Won Conversion Trend
        $funnelMonths = [];
        $funnelLeads = [];
        $funnelQuotes = [];
        $funnelAccepted = [];
        $funnelQuotedAmounts = [];
        $funnelAcceptedAmounts = [];
        $funnelConversionRates = [];

        foreach ($months as $month) {
            $mStart = $month->copy()->startOfMonth();
            $mEnd = $month->copy()->endOfMonth();

            $funnelMonths[] = $month->format('M Y');

            $mLeads = Lead::whereBetween('created_at', [$mStart, $mEnd])->count();
            $mQuotes = Quotation::whereBetween('created_at', [$mStart, $mEnd])->count();
            $mAccepted = Quotation::whereBetween('created_at', [$mStart, $mEnd])
                ->where('status', 'accepted')
                ->count();

            $mQuotedVal = (float) Quotation::whereBetween('created_at', [$mStart, $mEnd])->sum('total');
            $mAcceptedVal = (float) Quotation::whereBetween('created_at', [$mStart, $mEnd])
                ->where('status', 'accepted')
                ->sum('total');

            $mConversionRate = $mLeads > 0 ? round(($mAccepted / $mLeads) * 100, 1) : ($mQuotes > 0 ? round(($mAccepted / $mQuotes) * 100, 1) : 0.0);

            $funnelLeads[] = $mLeads;
            $funnelQuotes[] = $mQuotes;
            $funnelAccepted[] = $mAccepted;
            $funnelQuotedAmounts[] = round($mQuotedVal, 2);
            $funnelAcceptedAmounts[] = round($mAcceptedVal, 2);
            $funnelConversionRates[] = $mConversionRate;
        }

        // 5. Conversion Rate Metrics for Selected Date Range
        $rangeLeads = Lead::whereBetween('created_at', [$startDate, $endDate])->count();
        $rangeQuotes = Quotation::whereBetween('created_at', [$startDate, $endDate])->count();
        $rangeAcceptedQuotes = Quotation::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'accepted')
            ->count();
        $rangeRejectedQuotes = Quotation::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'rejected')
            ->count();

        $rangeQuotedTotal = (float) Quotation::whereBetween('created_at', [$startDate, $endDate])->sum('total');
        $rangeAcceptedTotal = (float) Quotation::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'accepted')
            ->sum('total');

        $leadToQuoteRate = $rangeLeads > 0 ? round(($rangeQuotes / $rangeLeads) * 100, 1) : 0.0;
        $quoteToAcceptedRate = $rangeQuotes > 0 ? round(($rangeAcceptedQuotes / $rangeQuotes) * 100, 1) : 0.0;
        $leadToWonRate = $rangeLeads > 0 ? round(($rangeAcceptedQuotes / $rangeLeads) * 100, 1) : 0.0;

        // Average Turnaround Time from Quote creation to acceptance
        $acceptedQuotations = Quotation::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'accepted')
            ->whereNotNull('accepted_at')
            ->get();

        $totalDaysToAccept = 0;
        foreach ($acceptedQuotations as $aq) {
            $totalDaysToAccept += max(1, $aq->created_at->diffInDays($aq->accepted_at));
        }
        $avgTurnaroundDays = $acceptedQuotations->count() > 0
            ? round($totalDaysToAccept / $acceptedQuotations->count(), 1)
            : 2.5;

        return [
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end'   => $endDate->format('Y-m-d'),
                'label' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'),
            ],
            'snapshot'   => [
                'active_contracts'    => $totalActiveContracts,
                'active_arr'          => round($activeArr, 2),
                'active_mrr'          => round($activeMrr, 2),
                'avg_contract_value'  => $avgContractValue,
                'expired_contracts'   => $expiredContracts,
                'cancelled_contracts' => $cancelledContracts,
                'retention_rate'      => $retentionRate,
                'churn_rate'          => $churnRate,
                'frequency_counts'    => $frequencyCounts,
                'frequency_mrr'       => $frequencyMrr,
            ],
            'timeline'   => [
                'labels'        => $mrrTimelineLabels,
                'mrr'           => $mrrValues,
                'arr'           => $arrValues,
                'new_contracts' => $newAmcCounts,
            ],
            'conversion' => [
                'range_leads'            => $rangeLeads,
                'range_quotes'           => $rangeQuotes,
                'range_accepted_quotes'  => $rangeAcceptedQuotes,
                'range_rejected_quotes'  => $rangeRejectedQuotes,
                'range_quoted_total'     => $rangeQuotedTotal,
                'range_accepted_total'   => $rangeAcceptedTotal,
                'lead_to_quote_rate'     => $leadToQuoteRate,
                'quote_to_accepted_rate' => $quoteToAcceptedRate,
                'lead_to_won_rate'       => $leadToWonRate,
                'avg_turnaround_days'    => $avgTurnaroundDays,
                'monthly_trend'          => [
                    'labels'            => $funnelMonths,
                    'leads'             => $funnelLeads,
                    'quotations'        => $funnelQuotes,
                    'accepted'          => $funnelAccepted,
                    'quoted_amounts'    => $funnelQuotedAmounts,
                    'accepted_amounts'  => $funnelAcceptedAmounts,
                    'conversion_rates'  => $funnelConversionRates,
                ]
            ]
        ];
    }
}
