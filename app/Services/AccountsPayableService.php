<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\VendorPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountsPayableService
{
    /**
     * Compute macro Accounts Payable & Vendor Aging Summary
     */
    public function getAccountsPayableSummary(?Carbon $asOfDate = null): array
    {
        $asOf = $asOfDate ?: now();

        $purchaseOrders = PurchaseOrder::with(['supplier', 'items', 'vendorPayments'])
            ->whereIn('status', ['ordered', 'partially_received', 'received'])
            ->whereIn('payment_status', ['unpaid', 'partially_paid'])
            ->orderBy('order_date')
            ->get();

        $totalPayableBalance = 0.0;
        $totalOverdueBalance = 0.0;
        $supplierIds = [];

        $buckets = [
            '0_30' => [
                'label'         => 'Current (0–30 Days)',
                'days_range'    => '0 to 30 Days',
                'amount'        => 0.0,
                'count'         => 0,
                'badge_class'   => 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 border-blue-300 dark:border-blue-800',
                'color'         => 'blue',
            ],
            '31_60' => [
                'label'         => '31–60 Days Overdue',
                'days_range'    => '31 to 60 Days',
                'amount'        => 0.0,
                'count'         => 0,
                'badge_class'   => 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border-amber-300 dark:border-amber-800',
                'color'         => 'amber',
            ],
            '61_90' => [
                'label'         => '61–90 Days Overdue',
                'days_range'    => '61 to 90 Days',
                'amount'        => 0.0,
                'count'         => 0,
                'badge_class'   => 'bg-orange-100 text-orange-800 dark:bg-orange-950/70 dark:text-orange-300 border-orange-300 dark:border-orange-800',
                'color'         => 'orange',
            ],
            '90_plus' => [
                'label'         => '90+ Days Critical',
                'days_range'    => 'Over 90 Days',
                'amount'        => 0.0,
                'count'         => 0,
                'badge_class'   => 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border-rose-300 dark:border-rose-800 animate-pulse',
                'color'         => 'rose',
            ],
        ];

        /** @var PurchaseOrder $po */
        foreach ($purchaseOrders as $po) {
            $balance = (float) $po->balanceDue();
            if ($balance <= 0.01) {
                continue;
            }

            $totalPayableBalance += $balance;
            if ($po->supplier_id) {
                $supplierIds[$po->supplier_id] = true;
            }

            // Calculate overdue age based on due_date or supplier_invoice_date or order_date + 30
            $dueDate = $po->due_date 
                ? Carbon::parse($po->due_date) 
                : ($po->supplier_invoice_date ? Carbon::parse($po->supplier_invoice_date)->addDays(30) : Carbon::parse($po->order_date)->addDays(30));

            $isOverdue = $asOf->gt($dueDate);
            $daysOverdue = $isOverdue ? (int) abs($asOf->diffInDays($dueDate)) : 0;

            if ($isOverdue) {
                $totalOverdueBalance += $balance;
            }

            if ($daysOverdue <= 30) {
                $bucketKey = '0_30';
            } elseif ($daysOverdue <= 60) {
                $bucketKey = '31_60';
            } elseif ($daysOverdue <= 90) {
                $bucketKey = '61_90';
            } else {
                $bucketKey = '90_plus';
            }

            $buckets[$bucketKey]['amount'] += $balance;
            $buckets[$bucketKey]['count']++;
        }

        // Compute DPO (Days Payable Outstanding) based on last 90 days total procurement spend
        $ninetyDaysAgo = $asOf->copy()->subDays(90);
        $totalSpendLast90Days = (float) PurchaseOrder::whereIn('status', ['ordered', 'partially_received', 'received'])
            ->whereBetween('order_date', [$ninetyDaysAgo->toDateString(), $asOf->toDateString()])
            ->sum('total');
        $dailyAvgSpend = $totalSpendLast90Days > 0 ? ($totalSpendLast90Days / 90.0) : 0.0;
        $dpoDays = $dailyAvgSpend > 0 ? round($totalPayableBalance / $dailyAvgSpend, 1) : 0.0;

        // Compute 3-Way Matching macro health stats across active POs
        $allActivePOs = PurchaseOrder::with(['items'])->whereIn('status', ['ordered', 'partially_received', 'received'])->get();
        $matchStats = [
            'matched'           => 0,
            'partial_receipt'   => 0,
            'unbilled'          => 0,
            'pending_inward'    => 0,
            'quantity_mismatch' => 0,
            'total'             => $allActivePOs->count(),
        ];

        /** @var PurchaseOrder $activePo */
        foreach ($allActivePOs as $activePo) {
            $match = $activePo->getThreeWayMatchStatus();
            $statusKey = $match['status'] ?? 'pending_inward';
            if (isset($matchStats[$statusKey])) {
                $matchStats[$statusKey]++;
            }
        }

        $passRate = $matchStats['total'] > 0 
            ? round(($matchStats['matched'] / $matchStats['total']) * 100, 1) 
            : 100.0;

        $totalUnpaidPos = $buckets['0_30']['count'] + $buckets['31_60']['count'] + $buckets['61_90']['count'] + $buckets['90_plus']['count'];

        return [
            'as_of_date'                 => $asOf->format('d M Y'),
            'total_payable_balance'      => round($totalPayableBalance, 2),
            'total_outstanding'          => round($totalPayableBalance, 2),
            'total_outstanding_payable'  => round($totalPayableBalance, 2),
            'total_overdue_balance'      => round($totalOverdueBalance, 2),
            'total_current_balance'      => round($buckets['0_30']['amount'], 2),
            'current_0_30'               => round($buckets['0_30']['amount'], 2),
            'overdue_31_60'              => round($buckets['31_60']['amount'], 2),
            'critical_61_90'             => round($buckets['61_90']['amount'], 2),
            'high_risk_90_plus'          => round($buckets['90_plus']['amount'], 2),
            'aging_0_30'                 => round($buckets['0_30']['amount'], 2),
            'aging_31_60'                => round($buckets['31_60']['amount'], 2),
            'aging_61_90'                => round($buckets['61_90']['amount'], 2),
            'aging_90_plus'              => round($buckets['90_plus']['amount'], 2),
            'suppliers_count'            => count($supplierIds),
            'total_suppliers_count'      => count($supplierIds),
            'total_pos_count'            => $allActivePOs->count(),
            'unpaid_pos_count'           => $totalUnpaidPos,
            'dpo_days'                   => $dpoDays,
            'three_way_match_pass_rate'  => $passRate,
            'three_way_stats'            => $matchStats,
            'buckets'                    => $buckets,
        ];
    }

    /**
     * Get Supplier-wise aggregated Accounts Payable Ledger
     */
    public function getSupplierPayablesList(array $filters = [], ?Carbon $asOfDate = null): array
    {
        $asOf = $asOfDate ?: now();

        $query = Supplier::whereHas('purchaseOrders', function ($q) {
            $q->whereIn('status', ['ordered', 'partially_received', 'received'])
              ->whereIn('payment_status', ['unpaid', 'partially_paid']);
        })->with(['purchaseOrders' => function ($q) {
            $q->whereIn('status', ['ordered', 'partially_received', 'received'])
              ->whereIn('payment_status', ['unpaid', 'partially_paid'])
              ->with(['items']);
        }]);

        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('company_name', 'like', "%{$s}%")
                  ->orWhere('gst_number', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $suppliers = $query->get();
        $payablesList = [];

        foreach ($suppliers as $supplier) {
            $orders = $supplier->purchaseOrders;
            $unpaidPoCount  = 0;
            $totalInvoiced  = 0.0;
            $totalPaid      = 0.0;
            $balanceDue     = 0.0;
            $days_0_30      = 0.0;
            $days_31_60     = 0.0;
            $days_61_90     = 0.0;
            $days_90_plus   = 0.0;
            $oldestDueDate  = null;
            $highestDaysOverdue = 0;

            foreach ($orders as $po) {
                $poBalance = (float) $po->balanceDue();
                if ($poBalance <= 0.01) {
                    continue;
                }

                $unpaidPoCount++;
                $totalInvoiced += (float) $po->total;
                $totalPaid     += (float) $po->amount_paid;
                $balanceDue    += $poBalance;

                $dueDate = $po->due_date 
                    ? Carbon::parse($po->due_date) 
                    : ($po->supplier_invoice_date ? Carbon::parse($po->supplier_invoice_date)->addDays(30) : Carbon::parse($po->order_date)->addDays(30));

                if (!$oldestDueDate || $dueDate->lt($oldestDueDate)) {
                    $oldestDueDate = $dueDate;
                }

                $poDaysOverdue = 0;
                if ($asOf->gt($dueDate)) {
                    $poDaysOverdue = (int) abs($asOf->diffInDays($dueDate));
                    if ($poDaysOverdue > $highestDaysOverdue) {
                        $highestDaysOverdue = $poDaysOverdue;
                    }
                }

                if ($poDaysOverdue <= 30) {
                    $days_0_30 += $poBalance;
                } elseif ($poDaysOverdue <= 60) {
                    $days_31_60 += $poBalance;
                } elseif ($poDaysOverdue <= 90) {
                    $days_61_90 += $poBalance;
                } else {
                    $days_90_plus += $poBalance;
                }
            }

            if ($balanceDue <= 0.01) {
                continue;
            }

            // Determine supplier aging bucket
            if ($highestDaysOverdue <= 30) {
                $bucketLabel = '0–30 Days (Current)';
                $bucketBadgeClass = 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 border-blue-300 dark:border-blue-800';
            } elseif ($highestDaysOverdue <= 60) {
                $bucketLabel = '31–60 Days Overdue';
                $bucketBadgeClass = 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border-amber-300 dark:border-amber-800';
            } elseif ($highestDaysOverdue <= 90) {
                $bucketLabel = '61–90 Days Overdue';
                $bucketBadgeClass = 'bg-orange-100 text-orange-800 dark:bg-orange-950/70 dark:text-orange-300 border-orange-300 dark:border-orange-800';
            } else {
                $bucketLabel = '90+ Days Critical';
                $bucketBadgeClass = 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border-rose-300 dark:border-rose-800 animate-pulse';
            }

            if (!empty($filters['bucket'])) {
                if ($filters['bucket'] === '0_30' && $highestDaysOverdue > 30) continue;
                if ($filters['bucket'] === '31_60' && ($highestDaysOverdue <= 30 || $highestDaysOverdue > 60)) continue;
                if ($filters['bucket'] === '61_90' && ($highestDaysOverdue <= 60 || $highestDaysOverdue > 90)) continue;
                if ($filters['bucket'] === '90_plus' && $highestDaysOverdue <= 90) continue;
            }

            $payablesList[] = [
                'supplier_id'          => $supplier->id,
                'supplier_name'        => $supplier->company_name ?: $supplier->name,
                'contact_person'       => $supplier->name,
                'phone'                => $supplier->phone ?? 'N/A',
                'email'                => $supplier->email ?? 'N/A',
                'gst_number'           => $supplier->gst_number ?? 'Unregistered',
                'address'              => $supplier->address ?? 'N/A',
                'city'                 => $supplier->city ?? '',
                'state'                => $supplier->state ?? '',
                'payment_terms'        => $supplier->payment_terms ?? 'Net 30',
                'unpaid_po_count'      => $unpaidPoCount,
                'days_0_30'            => round($days_0_30, 2),
                'days_31_60'           => round($days_31_60, 2),
                'days_61_90'           => round($days_61_90, 2),
                'days_90_plus'         => round($days_90_plus, 2),
                'total_invoiced'       => round($totalInvoiced, 2),
                'total_paid'           => round($totalPaid, 2),
                'balance_due'          => round($balanceDue, 2),
                'total_due'            => round($balanceDue, 2),
                'oldest_due_date'      => $oldestDueDate ? $oldestDueDate->format('d M Y') : 'N/A',
                'highest_days_overdue' => $highestDaysOverdue,
                'bucket_label'         => $bucketLabel,
                'bucket_badge_class'   => $bucketBadgeClass,
            ];
        }

        // Sort by balance due descending
        usort($payablesList, fn($a, $b) => $b['balance_due'] <=> $a['balance_due']);

        return $payablesList;
    }

    /**
     * Get 3-Way Matching Inspection List across all Purchase Orders
     */
    public function getThreeWayMatchingAuditList(array $filters = []): array
    {
        $query = PurchaseOrder::with(['supplier', 'items', 'vendorPayments'])->latest();

        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $query->where(function ($q) use ($s) {
                $q->where('po_number', 'like', "%{$s}%")
                  ->orWhere('supplier_invoice_no', 'like', "%{$s}%")
                  ->orWhereHas('supplier', function ($sq) use ($s) {
                      $sq->where('name', 'like', "%{$s}%")
                         ->orWhere('company_name', 'like', "%{$s}%");
                  });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $pos = $query->get();
        $auditList = [];

        foreach ($pos as $po) {
            $match = $po->getThreeWayMatchStatus();

            if (!empty($filters['match_status']) && $match['status'] !== $filters['match_status']) {
                continue;
            }

            $auditList[] = [
                'purchase_order'       => $po,
                'po_id'                => $po->id,
                'po_number'            => $po->po_number,
                'supplier_name'        => $po->supplier ? ($po->supplier->company_name ?: $po->supplier->name) : 'Supplier',
                'supplier_gst'         => $po->supplier?->gst_number ?? 'N/A',
                'order_date'           => $po->order_date ? Carbon::parse($po->order_date)->format('d M Y') : null,
                'expected_delivery'    => $po->expected_delivery_date ? Carbon::parse($po->expected_delivery_date)->format('d M Y') : 'N/A',
                'po_status'            => $po->status,
                'payment_status'       => $po->payment_status,
                'total_amount'         => (float) $po->total,
                'amount_paid'          => (float) $po->amount_paid,
                'balance_due'          => (float) $po->balanceDue(),
                'match_status'         => $match['status'],
                'match_label'          => $match['label'],
                'match_badge_class'    => $match['badge_class'],
                'match_description'    => $match['description'],
                'total_ordered_qty'    => $match['total_ordered'],
                'total_received_qty'   => $match['total_received'],
                'has_bill'             => $match['has_bill'],
                'bill_number'          => $match['bill_number'],
                'bill_date'            => $match['bill_date'],
                'items'                => $match['items'],
            ];
        }

        return $auditList;
    }

    /**
     * Get Pending Vendor Payment Disbursements (Due / Scheduled)
     */
    public function getPendingDisbursementsList(array $filters = []): array
    {
        $query = PurchaseOrder::with(['supplier', 'items'])
            ->whereIn('status', ['ordered', 'partially_received', 'received'])
            ->whereIn('payment_status', ['unpaid', 'partially_paid'])
            ->orderBy('due_date')
            ->orderBy('order_date');

        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $query->where(function ($q) use ($s) {
                $q->where('po_number', 'like', "%{$s}%")
                  ->orWhere('supplier_invoice_no', 'like', "%{$s}%")
                  ->orWhereHas('supplier', function ($sq) use ($s) {
                      $sq->where('name', 'like', "%{$s}%")
                         ->orWhere('company_name', 'like', "%{$s}%");
                  });
            });
        }

        $pos = $query->get();
        $disbursements = [];

        /** @var PurchaseOrder $po */
        foreach ($pos as $po) {
            $balance = (float) $po->balanceDue();
            if ($balance <= 0.01) {
                continue;
            }

            $dueDate = $po->due_date 
                ? Carbon::parse($po->due_date) 
                : ($po->supplier_invoice_date ? Carbon::parse($po->supplier_invoice_date)->addDays(30) : Carbon::parse($po->order_date)->addDays(30));

            $isOverdue = now()->gt($dueDate);
            $daysOverdue = $isOverdue ? (int) abs(now()->diffInDays($dueDate)) : 0;

            $disbursements[] = [
                'po'             => $po,
                'po_id'          => $po->id,
                'po_number'      => $po->po_number,
                'supplier_id'    => $po->supplier_id,
                'supplier_name'  => $po->supplier ? ($po->supplier->company_name ?: $po->supplier->name) : 'Supplier',
                'supplier_phone' => $po->supplier?->phone ?? 'N/A',
                'supplier_email' => $po->supplier?->email ?? null,
                'bill_number'    => $po->supplier_invoice_no ?: 'Pending Bill',
                'order_date'     => $po->order_date?->format('d M Y'),
                'due_date'       => $dueDate->format('d M Y'),
                'is_overdue'     => $isOverdue,
                'days_overdue'   => $daysOverdue,
                'total_amount'   => (float) $po->total,
                'amount_paid'    => (float) $po->amount_paid,
                'balance_due'    => $balance,
            ];
        }

        return $disbursements;
    }

    /**
     * Record Vendor Payment Disbursement & update Purchase Order balance
     */
    public function recordVendorPayment(array $data): VendorPayment
    {
        return DB::transaction(function () use ($data) {
            $amount = (float) $data['amount'];
            $poId = !empty($data['purchase_order_id']) ? (int) $data['purchase_order_id'] : null;
            $supplierId = (int) $data['supplier_id'];

            $reference = VendorPayment::generatePaymentReference();

            $payment = VendorPayment::create([
                'supplier_id'           => $supplierId,
                'purchase_order_id'     => $poId,
                'payment_reference'     => $reference,
                'payment_date'          => !empty($data['payment_date']) ? Carbon::parse($data['payment_date']) : now(),
                'amount'                => $amount,
                'payment_method'        => $data['payment_method'] ?? 'neft_rtgs',
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'notes'                 => $data['notes'] ?? null,
                'created_by'            => Auth::id() ?: ($data['created_by'] ?? null),
            ]);

            // If attached to specific PO, update PO amount_paid and status
            if ($poId) {
                $po = PurchaseOrder::find($poId);
                if ($po) {
                    $newPaid = (float) $po->amount_paid + $amount;
                    $status = $newPaid >= (float) $po->total ? 'paid' : 'partially_paid';

                    $po->update([
                        'amount_paid'    => $newPaid,
                        'payment_status' => $status,
                    ]);
                }
            }

            return $payment;
        });
    }

    /**
     * Build Chronological Statement of Account Ledger for a specific Supplier
     */
    public function getSupplierStatementLedger(Supplier $supplier): array
    {
        $supplier->load([
            'purchaseOrders.items',
            'vendorPayments',
        ]);

        $transactions = [];
        $runningBalance = 0.0;
        $totalInvoiced = 0.0;
        $totalPaid = 0.0;

        // Collect all POs and Payments
        $rawEvents = [];

        foreach ($supplier->purchaseOrders as $po) {
            $date = $po->order_date ? Carbon::parse($po->order_date) : $po->created_at;
            $rawEvents[] = [
                'date'        => $date,
                'type'        => 'purchase_order',
                'po_id'       => $po->id,
                'reference'   => $po->po_number . ($po->supplier_invoice_no ? " (Bill: {$po->supplier_invoice_no})" : ''),
                'description' => "Procurement PO #{$po->po_number} - Status: " . ucfirst($po->status),
                'credit'      => (float) $po->total, // Increases our liability to supplier
                'debit'       => 0.0,
                'raw_date'    => $date->timestamp,
            ];
            $totalInvoiced += (float) $po->total;
        }

        foreach ($supplier->vendorPayments as $pmt) {
            $date = $pmt->payment_date ? Carbon::parse($pmt->payment_date) : $pmt->created_at;
            $rawEvents[] = [
                'date'        => $date,
                'type'        => 'payment',
                'po_id'       => $pmt->purchase_order_id,
                'reference'   => $pmt->payment_reference,
                'description' => "Disbursement via " . strtoupper(str_replace('_', ' ', $pmt->payment_method)) . ($pmt->transaction_reference ? " (Ref: {$pmt->transaction_reference})" : ''),
                'credit'      => 0.0,
                'debit'       => (float) $pmt->amount, // Decreases our liability
                'raw_date'    => $date->timestamp,
            ];
            $totalPaid += (float) $pmt->amount;
        }

        // Sort events chronologically
        usort($rawEvents, fn($a, $b) => $a['raw_date'] <=> $b['raw_date']);

        foreach ($rawEvents as $event) {
            $runningBalance += ($event['credit'] - $event['debit']);
            $event['balance'] = max(0.0, $runningBalance);
            $event['running_balance'] = max(0.0, $runningBalance);
            $event['details'] = $event['description'];
            $transactions[] = $event;
        }

        $outstanding = max(0.0, $totalInvoiced - $totalPaid);

        return [
            'transactions'        => $transactions,
            'total_invoiced'      => round($totalInvoiced, 2),
            'total_billed'        => round($totalInvoiced, 2),
            'total_paid'          => round($totalPaid, 2),
            'outstanding_balance' => round($outstanding, 2),
            'net_balance'         => round($outstanding, 2),
        ];
    }

    /**
     * Export Accounts Payable Aging CSV Spreadsheet
     */
    public function exportApAgingCsv(): string
    {
        $summary = $this->getAccountsPayableSummary();
        $suppliers = $this->getSupplierPayablesList();

        $output = fopen('php://temp', 'r+');

        fputcsv($output, ['--- VENDOR ACCOUNTS PAYABLE & AGING LEDGER ---']);
        fputcsv($output, ['Generated As Of', $summary['as_of_date']]);
        fputcsv($output, ['Total Outstanding Payables (INR)', $summary['total_payable_balance']]);
        fputcsv($output, ['Current (0-30 Days)', $summary['current_0_30']]);
        fputcsv($output, ['31-60 Days Overdue', $summary['overdue_31_60']]);
        fputcsv($output, ['61-90 Days Overdue', $summary['critical_61_90']]);
        fputcsv($output, ['90+ Days Critical', $summary['high_risk_90_plus']]);
        fputcsv($output, ['Days Payable Outstanding (DPO)', $summary['dpo_days'] . ' Days']);
        fputcsv($output, ['3-Way Matching Pass Rate', $summary['three_way_match_pass_rate'] . '%']);
        fputcsv($output, []);

        fputcsv($output, [
            'Supplier ID',
            'Supplier Company',
            'Contact Person',
            'Phone',
            'Email',
            'GSTIN',
            'Payment Terms',
            'Unpaid POs',
            '0-30 Days (Current)',
            '31-60 Days',
            '61-90 Days',
            '90+ Days',
            'Total Invoiced',
            'Total Paid',
            'Net Payable Due',
            'Oldest Due Date',
            'Days Overdue',
            'Aging Status',
        ]);

        foreach ($suppliers as $row) {
            fputcsv($output, [
                $row['supplier_id'],
                $row['supplier_name'],
                $row['contact_person'],
                $row['phone'],
                $row['email'],
                $row['gst_number'],
                $row['payment_terms'],
                $row['unpaid_po_count'],
                $row['days_0_30'],
                $row['days_31_60'],
                $row['days_61_90'],
                $row['days_90_plus'],
                $row['total_invoiced'],
                $row['total_paid'],
                $row['balance_due'],
                $row['oldest_due_date'],
                $row['highest_days_overdue'],
                $row['bucket_label'],
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Export 3-Way Matching Audit CSV Spreadsheet
     */
    public function exportThreeWayMatchCsv(): string
    {
        $auditList = $this->getThreeWayMatchingAuditList();

        $output = fopen('php://temp', 'r+');

        fputcsv($output, ['--- 3-WAY MATCHING PROCUREMENT AUDIT LEDGER ---']);
        fputcsv($output, ['Exported At', now()->format('d M Y, h:i A')]);
        fputcsv($output, []);

        fputcsv($output, [
            'PO Number',
            'Supplier Name',
            'Supplier GSTIN',
            'Order Date',
            'PO Status',
            'Ordered Qty',
            'Received Qty (GRN)',
            'Vendor Bill #',
            'Vendor Bill Date',
            'Total Amount (INR)',
            'Amount Paid',
            'Balance Due',
            '3-Way Match Status',
            'Audit Notes',
        ]);

        foreach ($auditList as $row) {
            fputcsv($output, [
                $row['po_number'],
                $row['supplier_name'],
                $row['supplier_gst'],
                $row['order_date'],
                $row['po_status'],
                $row['total_ordered_qty'],
                $row['total_received_qty'],
                $row['bill_number'] ?: 'Unbilled',
                $row['bill_date'] ?: 'N/A',
                $row['total_amount'],
                $row['amount_paid'],
                $row['balance_due'],
                $row['match_label'],
                $row['match_description'],
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
