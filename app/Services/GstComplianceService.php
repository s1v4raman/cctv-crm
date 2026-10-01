<?php

namespace App\Services;

use App\Models\ExpenseClaim;
use App\Models\GatewaySetting;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GstComplianceService
{
    /**
     * Get Company GST and Location Profile
     */
    public function getCompanyGstDetails(): array
    {
        $settings = GatewaySetting::getSettings();

        return [
            'trade_name' => $settings->company_trade_name ?: 'Precision IT Systems',
            'legal_name' => $settings->company_legal_name ?: 'Precision IT Systems',
            'gstin'      => $settings->company_gstin ?: '33AHLPI3531N1Z8',
            'pan'        => $settings->company_pan ?: substr($settings->company_gstin ?: '33AHLPI3531N1Z8', 2, 10),
            'state'      => $settings->company_state ?: 'Tamil Nadu',
            'state_code' => $settings->company_state_code ?: '33',
            'address'    => $settings->company_address ?: 'Plot No.553, Lig-1, 27th Street, Tamil Nadu Housing Board, Avadi, Chennai-600054.',
        ];
    }

    /**
     * Compile GSTR-1 Outward Supplies (Sales) Data
     */
    public function getGstr1Data(Carbon $startDate, Carbon $endDate): array
    {
        $company = $this->getCompanyGstDetails();
        $companyStateCode = $company['state_code'];

        $invoices = Invoice::with(['quotation.lead', 'quotation.items.product', 'installationJob'])
            ->whereBetween('invoice_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('invoice_date')
            ->get();

        $b2bInvoices = [];
        $b2cLargeInvoices = [];
        $b2cSmallSummary = []; // Keyed by pos_code + rate
        $hsnData = [];         // Keyed by hsn_code

        $totalGrossTurnover = 0.0;
        $totalTaxableTurnover = 0.0;
        $totalOutputCgst = 0.0;
        $totalOutputSgst = 0.0;
        $totalOutputIgst = 0.0;
        $totalOutputTax = 0.0;

        foreach ($invoices as $inv) {
            $lead = $inv->quotation?->lead;
            $customerGstin = trim((string) ($lead?->gstin ?? ''));
            $isB2b = !empty($customerGstin) || $inv->is_b2b;

            $posState = $inv->place_of_supply ?: ($lead?->state ?: $company['state']);
            $posCode  = $inv->place_of_supply_code ?: ($lead?->state_code ?: $companyStateCode);
            $isIntraState = ($posCode === $companyStateCode);

            $taxable = (float) $inv->taxableAmount();
            $taxPercent = (float) ($inv->tax_percent > 0 ? $inv->tax_percent : 18.00);
            $totalTax = (float) ($inv->tax_amount > 0 ? $inv->tax_amount : ($taxable * ($taxPercent / 100.0)));
            $invoiceTotal = (float) $inv->total;

            // Compute CGST, SGST, IGST splits
            if ($isIntraState) {
                $cgst = round($totalTax / 2.0, 2);
                $sgst = round($totalTax - $cgst, 2);
                $igst = 0.0;
            } else {
                $cgst = 0.0;
                $sgst = 0.0;
                $igst = round($totalTax, 2);
            }

            $totalGrossTurnover   += $invoiceTotal;
            $totalTaxableTurnover += $taxable;
            $totalOutputCgst      += $cgst;
            $totalOutputSgst      += $sgst;
            $totalOutputIgst      += $igst;
            $totalOutputTax       += $totalTax;

            if ($isB2b) {
                // Table 4: B2B Invoices
                $b2bInvoices[] = [
                    'invoice_id'     => $inv->id,
                    'invoice_no'     => $inv->invoice_no,
                    'invoice_date'   => $inv->invoice_date?->format('d-m-Y'),
                    'customer_name'  => $lead?->company_legal_name ?: ($lead?->customer_name ?: 'B2B Client'),
                    'customer_gstin' => $customerGstin ?: 'Unspecified GSTIN',
                    'pos_state'      => $posState,
                    'pos_code'       => $posCode,
                    'is_intra_state' => $isIntraState,
                    'taxable_value'  => $taxable,
                    'tax_rate'       => $taxPercent,
                    'cgst'           => $cgst,
                    'sgst'           => $sgst,
                    'igst'           => $igst,
                    'total_tax'      => $totalTax,
                    'total_value'    => $invoiceTotal,
                    'status'         => $inv->status,
                ];
            } else {
                // Unregistered B2C
                if (!$isIntraState && $invoiceTotal > 250000) {
                    // Table 5: B2C Large (Inter-state unregistered > 2.5L)
                    $b2cLargeInvoices[] = [
                        'invoice_id'     => $inv->id,
                        'invoice_no'     => $inv->invoice_no,
                        'invoice_date'   => $inv->invoice_date?->format('d-m-Y'),
                        'customer_name'  => $lead?->customer_name ?: 'Retail Customer',
                        'pos_state'      => $posState,
                        'pos_code'       => $posCode,
                        'taxable_value'  => $taxable,
                        'tax_rate'       => $taxPercent,
                        'igst'           => $igst,
                        'total_value'    => $invoiceTotal,
                    ];
                } else {
                    // Table 7: B2C Small (Aggregated by POS and Rate)
                    $groupKey = $posCode . '_' . (int) $taxPercent;
                    if (!isset($b2cSmallSummary[$groupKey])) {
                        $b2cSmallSummary[$groupKey] = [
                            'pos_state'      => $posState,
                            'pos_code'       => $posCode,
                            'tax_rate'       => $taxPercent,
                            'is_intra_state' => $isIntraState,
                            'taxable_value'  => 0.0,
                            'cgst'           => 0.0,
                            'sgst'           => 0.0,
                            'igst'           => 0.0,
                            'total_tax'      => 0.0,
                            'total_value'    => 0.0,
                            'invoice_count'  => 0,
                        ];
                    }
                    $b2cSmallSummary[$groupKey]['taxable_value'] += $taxable;
                    $b2cSmallSummary[$groupKey]['cgst']          += $cgst;
                    $b2cSmallSummary[$groupKey]['sgst']          += $sgst;
                    $b2cSmallSummary[$groupKey]['igst']          += $igst;
                    $b2cSmallSummary[$groupKey]['total_tax']     += $totalTax;
                    $b2cSmallSummary[$groupKey]['total_value']   += $invoiceTotal;
                    $b2cSmallSummary[$groupKey]['invoice_count']++;
                }
            }

            // Table 12: HSN Summary Aggregation
            $quotationItems = $inv->quotation?->items ?? collect();
            if ($quotationItems->count() > 0) {
                foreach ($quotationItems as $item) {
                    $product = $item->product;
                    $hsn = $product?->hsn_code ?: ($item->item_name && str_contains(strtolower($item->item_name), 'service') ? '9987' : '8525');
                    $desc = $product?->name ?: ($item->item_name ?: 'CCTV Equipment / Service');
                    $qty = (float) ($item->quantity ?: 1.0);
                    $uqc = strtoupper($product?->unit ?: ($item->unit ?: 'NOS'));
                    $itemTaxable = (float) ($item->total > 0 ? $item->total : ($qty * (float) $item->unit_price));
                    $itemTax = round($itemTaxable * ($taxPercent / 100.0), 2);

                    $itemCgst = $isIntraState ? round($itemTax / 2.0, 2) : 0.0;
                    $itemSgst = $isIntraState ? round($itemTax - $itemCgst, 2) : 0.0;
                    $itemIgst = !$isIntraState ? $itemTax : 0.0;

                    if (!isset($hsnData[$hsn])) {
                        $hsnData[$hsn] = [
                            'hsn_code'      => $hsn,
                            'description'   => $desc,
                            'uqc'           => $uqc,
                            'total_qty'     => 0.0,
                            'total_value'   => 0.0,
                            'taxable_value' => 0.0,
                            'tax_rate'      => $taxPercent,
                            'cgst'          => 0.0,
                            'sgst'          => 0.0,
                            'igst'          => 0.0,
                            'total_tax'     => 0.0,
                        ];
                    }

                    $hsnData[$hsn]['total_qty']     += $qty;
                    $hsnData[$hsn]['taxable_value'] += $itemTaxable;
                    $hsnData[$hsn]['total_tax']     += $itemTax;
                    $hsnData[$hsn]['cgst']          += $itemCgst;
                    $hsnData[$hsn]['sgst']          += $itemSgst;
                    $hsnData[$hsn]['igst']          += $itemIgst;
                    $hsnData[$hsn]['total_value']   += ($itemTaxable + $itemTax);
                }
            } else {
                // Generic HSN if invoice items not broken down
                $hsn = '8525';
                if (!isset($hsnData[$hsn])) {
                    $hsnData[$hsn] = [
                        'hsn_code'      => $hsn,
                        'description'   => 'CCTV Security & Surveillance Equipment',
                        'uqc'           => 'SET',
                        'total_qty'     => 0.0,
                        'total_value'   => 0.0,
                        'taxable_value' => 0.0,
                        'tax_rate'      => $taxPercent,
                        'cgst'          => 0.0,
                        'sgst'          => 0.0,
                        'igst'          => 0.0,
                        'total_tax'     => 0.0,
                    ];
                }
                $hsnData[$hsn]['total_qty']     += 1;
                $hsnData[$hsn]['taxable_value'] += $taxable;
                $hsnData[$hsn]['total_tax']     += $totalTax;
                $hsnData[$hsn]['cgst']          += $cgst;
                $hsnData[$hsn]['sgst']          += $sgst;
                $hsnData[$hsn]['igst']          += $igst;
                $hsnData[$hsn]['total_value']   += $invoiceTotal;
            }
        }

        return [
            'period'                 => $startDate->format('F Y'),
            'company'                => $company,
            'b2b_invoices'           => $b2bInvoices,
            'b2c_large_invoices'     => $b2cLargeInvoices,
            'b2c_small_summary'      => array_values($b2cSmallSummary),
            'hsn_summary'            => array_values($hsnData),
            'total_invoices_count'   => $invoices->count(),
            'total_gross_turnover'   => round($totalGrossTurnover, 2),
            'total_taxable_turnover' => round($totalTaxableTurnover, 2),
            'total_output_cgst'      => round($totalOutputCgst, 2),
            'total_output_sgst'      => round($totalOutputSgst, 2),
            'total_output_igst'      => round($totalOutputIgst, 2),
            'total_output_tax'       => round($totalOutputTax, 2),
        ];
    }

    /**
     * Compile Input Tax Credit (ITC) from Vendor POs and Field Expenses
     */
    public function getItcData(Carbon $startDate, Carbon $endDate): array
    {
        $company = $this->getCompanyGstDetails();
        $companyStateCode = $company['state_code'];

        // 1. Inward supplies from Purchase Orders
        $purchaseOrders = PurchaseOrder::with(['supplier', 'items.product'])
            ->whereBetween('order_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('status', '!=', 'cancelled')
            ->where('is_itc_eligible', true)
            ->orderBy('order_date')
            ->get();

        $poItcRecords = [];
        $totalPoTaxable = 0.0;
        $totalPoCgst = 0.0;
        $totalPoSgst = 0.0;
        $totalPoIgst = 0.0;
        $totalPoItc = 0.0;

        foreach ($purchaseOrders as $po) {
            $supplier = $po->supplier;
            $supplierGstin = trim((string) ($supplier?->gst_number ?? ''));
            $supplierState = $supplier?->state ?: $company['state'];
            
            $isIntraState = true;
            if ($supplierGstin && strlen($supplierGstin) >= 2) {
                $suppStateCode = substr($supplierGstin, 0, 2);
                $isIntraState = ($suppStateCode === $companyStateCode);
            }

            $taxable = (float) $po->subtotal;
            $taxPercent = (float) ($po->tax_percent ?: 18.00);
            $totalTax = (float) ($po->tax_amount ?: ($taxable * ($taxPercent / 100.0)));

            if ($isIntraState) {
                $cgst = round($totalTax / 2.0, 2);
                $sgst = round($totalTax - $cgst, 2);
                $igst = 0.0;
            } else {
                $cgst = 0.0;
                $sgst = 0.0;
                $igst = round($totalTax, 2);
            }

            $totalPoTaxable += $taxable;
            $totalPoCgst    += $cgst;
            $totalPoSgst    += $sgst;
            $totalPoIgst    += $igst;
            $totalPoItc     += $totalTax;

            $poItcRecords[] = [
                'po_id'             => $po->id,
                'po_number'         => $po->po_number,
                'supplier_inv_no'   => $po->supplier_invoice_no ?: $po->po_number,
                'supplier_inv_date' => ($po->supplier_invoice_date ?: $po->order_date)?->format('d-m-Y'),
                'supplier_name'     => $supplier?->name ?: 'Vendor Supplier',
                'supplier_gstin'    => $supplierGstin ?: 'Unregistered Vendor',
                'supplier_state'    => $supplierState,
                'is_intra_state'    => $isIntraState,
                'taxable_value'     => $taxable,
                'tax_rate'          => $taxPercent,
                'cgst'              => $cgst,
                'sgst'              => $sgst,
                'igst'              => $igst,
                'itc_amount'        => $totalTax,
                'total_amount'      => (float) $po->total,
                'status'            => $po->status,
            ];
        }

        // 2. Operational Hardware & Supplies Expense Claims
        $expenses = ExpenseClaim::whereBetween('expense_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereIn('status', ['approved', 'paid'])
            ->whereIn('expense_category', ['hardware_tools', 'emergency_materials'])
            ->with(['user'])
            ->get();

        $expenseItcRecords = [];
        $totalExpTaxable = 0.0;
        $totalExpCgst = 0.0;
        $totalExpSgst = 0.0;
        $totalExpItc = 0.0;

        foreach ($expenses as $exp) {
            // Assume 18% GST inclusive on emergency hardware tools/cables
            $gross = (float) $exp->amount;
            $taxable = round($gross / 1.18, 2);
            $tax = round($gross - $taxable, 2);
            $cgst = round($tax / 2.0, 2);
            $sgst = round($tax - $cgst, 2);

            $totalExpTaxable += $taxable;
            $totalExpCgst    += $cgst;
            $totalExpSgst    += $sgst;
            $totalExpItc     += $tax;

            $expenseItcRecords[] = [
                'claim_id'       => $exp->id,
                'claim_no'       => $exp->claim_no,
                'expense_date'   => $exp->expense_date?->format('d-m-Y'),
                'employee_name'  => $exp->user?->name ?? 'Technician',
                'category'       => $exp->category_label,
                'description'    => $exp->description,
                'gross_amount'   => $gross,
                'taxable_value'  => $taxable,
                'cgst'           => $cgst,
                'sgst'           => $sgst,
                'itc_amount'     => $tax,
            ];
        }

        $grandTotalItc = $totalPoItc + $totalExpItc;
        $grandTotalCgst = $totalPoCgst + $totalExpCgst;
        $grandTotalSgst = $totalPoSgst + $totalExpSgst;
        $grandTotalIgst = $totalPoIgst;

        return [
            'period'               => $startDate->format('F Y'),
            'purchase_orders'      => $poItcRecords,
            'expenses'             => $expenseItcRecords,
            'total_po_taxable'     => round($totalPoTaxable, 2),
            'total_po_itc'         => round($totalPoItc, 2),
            'total_expense_itc'    => round($totalExpItc, 2),
            'grand_total_taxable'  => round($totalPoTaxable + $totalExpTaxable, 2),
            'grand_total_cgst_itc' => round($grandTotalCgst, 2),
            'grand_total_sgst_itc' => round($grandTotalSgst, 2),
            'grand_total_igst_itc' => round($grandTotalIgst, 2),
            'grand_total_itc'      => round($grandTotalItc, 2),
        ];
    }

    /**
     * Compile GSTR-3B Summary & Tax Set-Off Computation
     */
    public function getGstr3bSummary(Carbon $startDate, Carbon $endDate): array
    {
        $gstr1 = $this->getGstr1Data($startDate, $endDate);
        $itc   = $this->getItcData($startDate, $endDate);

        // Output Tax Liability
        $outIgst = $gstr1['total_output_igst'];
        $outCgst = $gstr1['total_output_cgst'];
        $outSgst = $gstr1['total_output_sgst'];
        $totalOutputTax = $gstr1['total_output_tax'];

        // Available Input Tax Credit
        $inIgst = $itc['grand_total_igst_itc'];
        $inCgst = $itc['grand_total_cgst_itc'];
        $inSgst = $itc['grand_total_sgst_itc'];
        $totalItc = $itc['grand_total_itc'];

        // -------------------------------------------------------------
        // Indian GST Set-Off Rules:
        // 1. IGST Credit used against: (a) IGST Output, (b) CGST Output, (c) SGST Output
        // 2. CGST Credit used against: (a) CGST Output, (b) IGST Output (cannot use against SGST)
        // 3. SGST Credit used against: (a) SGST Output, (b) IGST Output (cannot use against CGST)
        // -------------------------------------------------------------
        
        $remInIgst = $inIgst;
        $remInCgst = $inCgst;
        $remInSgst = $inSgst;

        $balOutIgst = $outIgst;
        $balOutCgst = $outCgst;
        $balOutSgst = $outSgst;

        // Step 1: Offset IGST Output with IGST Input
        $igstSetAgainstIgst = min($balOutIgst, $remInIgst);
        $balOutIgst -= $igstSetAgainstIgst;
        $remInIgst  -= $igstSetAgainstIgst;

        // Step 2: Offset CGST Output with remaining IGST Input
        $igstSetAgainstCgst = min($balOutCgst, $remInIgst);
        $balOutCgst -= $igstSetAgainstCgst;
        $remInIgst  -= $igstSetAgainstCgst;

        // Step 3: Offset SGST Output with remaining IGST Input
        $igstSetAgainstSgst = min($balOutSgst, $remInIgst);
        $balOutSgst -= $igstSetAgainstSgst;
        $remInIgst  -= $igstSetAgainstSgst;

        // Step 4: Offset CGST Output with CGST Input
        $cgstSetAgainstCgst = min($balOutCgst, $remInCgst);
        $balOutCgst -= $cgstSetAgainstCgst;
        $remInCgst  -= $cgstSetAgainstCgst;

        // Step 5: Offset SGST Output with SGST Input
        $sgstSetAgainstSgst = min($balOutSgst, $remInSgst);
        $balOutSgst -= $sgstSetAgainstSgst;
        $remInSgst  -= $sgstSetAgainstSgst;

        // Step 6: Offset remaining IGST Output with CGST Input then SGST Input
        if ($balOutIgst > 0 && $remInCgst > 0) {
            $cgstSetAgainstIgst = min($balOutIgst, $remInCgst);
            $balOutIgst -= $cgstSetAgainstIgst;
            $remInCgst  -= $cgstSetAgainstIgst;
        }

        if ($balOutIgst > 0 && $remInSgst > 0) {
            $sgstSetAgainstIgst = min($balOutIgst, $remInSgst);
            $balOutIgst -= $sgstSetAgainstIgst;
            $remInSgst  -= $sgstSetAgainstIgst;
        }

        // Net Cash Tax Payable to Government
        $netCashIgst = round(max(0, $balOutIgst), 2);
        $netCashCgst = round(max(0, $balOutCgst), 2);
        $netCashSgst = round(max(0, $balOutSgst), 2);
        $netCashPayable = round($netCashIgst + $netCashCgst + $netCashSgst, 2);

        // Closing ITC Carry Forward
        $closingItcIgst = round($remInIgst, 2);
        $closingItcCgst = round($remInCgst, 2);
        $closingItcSgst = round($remInSgst, 2);
        $totalClosingItc = round($closingItcIgst + $closingItcCgst + $closingItcSgst, 2);

        return [
            'period'               => $startDate->format('F Y'),
            'turnover_taxable'     => $gstr1['total_taxable_turnover'],
            'turnover_gross'       => $gstr1['total_gross_turnover'],
            'output_tax' => [
                'igst'  => $outIgst,
                'cgst'  => $outCgst,
                'sgst'  => $outSgst,
                'total' => $totalOutputTax,
            ],
            'input_tax_credit' => [
                'igst'  => $inIgst,
                'cgst'  => $inCgst,
                'sgst'  => $inSgst,
                'total' => $totalItc,
            ],
            'set_off_matrix' => [
                'igst_used' => [
                    'against_igst' => $igstSetAgainstIgst,
                    'against_cgst' => $igstSetAgainstCgst,
                    'against_sgst' => $igstSetAgainstSgst,
                ],
                'cgst_used' => [
                    'against_cgst' => $cgstSetAgainstCgst,
                ],
                'sgst_used' => [
                    'against_sgst' => $sgstSetAgainstSgst,
                ],
            ],
            'net_tax_payable_cash' => [
                'igst'  => $netCashIgst,
                'cgst'  => $netCashCgst,
                'sgst'  => $netCashSgst,
                'total' => $netCashPayable,
            ],
            'itc_carry_forward' => [
                'igst'  => $closingItcIgst,
                'cgst'  => $closingItcCgst,
                'sgst'  => $closingItcSgst,
                'total' => $totalClosingItc,
            ],
        ];
    }

    /**
     * Generate GST Portal Ready GSTR-1 JSON Payload
     */
    public function exportGstr1Json(Carbon $startDate, Carbon $endDate): array
    {
        $gstr1 = $this->getGstr1Data($startDate, $endDate);
        $company = $gstr1['company'];
        $fp = $startDate->format('mY'); // e.g. '092026'

        // Group B2B by customer GSTIN
        $b2bGrouped = [];
        foreach ($gstr1['b2b_invoices'] as $inv) {
            $gstin = $inv['customer_gstin'];
            if (!isset($b2bGrouped[$gstin])) {
                $b2bGrouped[$gstin] = [
                    'ctin' => $gstin,
                    'inv'  => [],
                ];
            }

            $b2bGrouped[$gstin]['inv'][] = [
                'inum'   => $inv['invoice_no'],
                'idt'    => $inv['invoice_date'],
                'val'    => $inv['total_value'],
                'pos'    => str_pad((string) $inv['pos_code'], 2, '0', STR_PAD_LEFT),
                'rchrg'  => 'N',
                'inv_typ'=> 'R',
                'itms'   => [
                    [
                        'num' => 1,
                        'itm_det' => [
                            'rt'   => $inv['tax_rate'],
                            'txval'=> $inv['taxable_value'],
                            'iamt' => $inv['igst'],
                            'camt' => $inv['cgst'],
                            'samt' => $inv['sgst'],
                            'csamt'=> 0.0,
                        ],
                    ],
                ],
            ];
        }

        // B2C Small
        $b2csPayload = [];
        foreach ($gstr1['b2c_small_summary'] as $b2cs) {
            $b2csPayload[] = [
                'sply_ty' => $b2cs['is_intra_state'] ? 'INTRA' : 'INTER',
                'pos'     => str_pad((string) $b2cs['pos_code'], 2, '0', STR_PAD_LEFT),
                'rt'      => $b2cs['tax_rate'],
                'txval'   => $b2cs['taxable_value'],
                'iamt'    => $b2cs['igst'],
                'camt'    => $b2cs['cgst'],
                'samt'    => $b2cs['sgst'],
                'csamt'   => 0.0,
            ];
        }

        // HSN Summary
        $hsnPayload = [];
        $num = 1;
        foreach ($gstr1['hsn_summary'] as $h) {
            $hsnPayload[] = [
                'num'   => $num++,
                'hsn_sc'=> $h['hsn_code'],
                'desc'  => $h['description'],
                'uqc'   => $h['uqc'],
                'qty'   => $h['total_qty'],
                'val'   => $h['total_value'],
                'txval' => $h['taxable_value'],
                'iamt'  => $h['igst'],
                'camt'  => $h['cgst'],
                'samt'  => $h['sgst'],
                'csamt' => 0.0,
            ];
        }

        return [
            'gstin'  => $company['gstin'],
            'fp'     => $fp,
            'gt'     => $gstr1['total_gross_turnover'],
            'cur_gt' => $gstr1['total_gross_turnover'],
            'b2b'    => array_values($b2bGrouped),
            'b2cl'   => $gstr1['b2c_large_invoices'],
            'b2cs'   => $b2csPayload,
            'hsn'    => [
                'data' => $hsnPayload,
            ],
        ];
    }

    /**
     * Generate GSTR-1 CSV formatted file content
     */
    public function exportGstr1Csv(Carbon $startDate, Carbon $endDate): string
    {
        $gstr1 = $this->getGstr1Data($startDate, $endDate);
        
        $output = fopen('php://temp', 'r+');

        // Header Section
        fputcsv($output, ['--- GSTR-1 OUTWARD SALES RETURN SUMMARY ---']);
        fputcsv($output, ['Company Trade Name', $gstr1['company']['trade_name']]);
        fputcsv($output, ['Company GSTIN', $gstr1['company']['gstin']]);
        fputcsv($output, ['Filing Period', $gstr1['period']]);
        fputcsv($output, ['Total Invoices', $gstr1['total_invoices_count']]);
        fputcsv($output, ['Total Gross Turnover (₹)', number_format($gstr1['total_gross_turnover'], 2, '.', '')]);
        fputcsv($output, ['Total Taxable Turnover (₹)', number_format($gstr1['total_taxable_turnover'], 2, '.', '')]);
        fputcsv($output, ['Total Output CGST (₹)', number_format($gstr1['total_output_cgst'], 2, '.', '')]);
        fputcsv($output, ['Total Output SGST (₹)', number_format($gstr1['total_output_sgst'], 2, '.', '')]);
        fputcsv($output, ['Total Output IGST (₹)', number_format($gstr1['total_output_igst'], 2, '.', '')]);
        fputcsv($output, ['Total Output Tax (₹)', number_format($gstr1['total_output_tax'], 2, '.', '')]);
        fputcsv($output, []);

        // Table 4: B2B Invoices
        fputcsv($output, ['--- TABLE 4: B2B INVOICES (TAX INVOICES WITH CUSTOMER GSTIN) ---']);
        fputcsv($output, ['Invoice Number', 'Invoice Date', 'Customer Legal Name', 'Customer GSTIN', 'Place of Supply', 'Taxable Value (₹)', 'Rate (%)', 'CGST (₹)', 'SGST (₹)', 'IGST (₹)', 'Total Tax (₹)', 'Invoice Total (₹)']);
        foreach ($gstr1['b2b_invoices'] as $row) {
            fputcsv($output, [
                $row['invoice_no'],
                $row['invoice_date'],
                $row['customer_name'],
                $row['customer_gstin'],
                $row['pos_state'] . ' (' . $row['pos_code'] . ')',
                number_format($row['taxable_value'], 2, '.', ''),
                $row['tax_rate'],
                number_format($row['cgst'], 2, '.', ''),
                number_format($row['sgst'], 2, '.', ''),
                number_format($row['igst'], 2, '.', ''),
                number_format($row['total_tax'], 2, '.', ''),
                number_format($row['total_value'], 2, '.', ''),
            ]);
        }
        fputcsv($output, []);

        // Table 7: B2C Small Invoices
        fputcsv($output, ['--- TABLE 7: B2C SMALL SUMMARY (RETAIL / UNREGISTERED CUSTOMERS) ---']);
        fputcsv($output, ['Place of Supply', 'Supply Type', 'Tax Rate (%)', 'Invoices Count', 'Taxable Value (₹)', 'CGST (₹)', 'SGST (₹)', 'IGST (₹)', 'Total Tax (₹)', 'Total Value (₹)']);
        foreach ($gstr1['b2c_small_summary'] as $b) {
            fputcsv($output, [
                $b['pos_state'] . ' (' . $b['pos_code'] . ')',
                $b['is_intra_state'] ? 'Intra-State' : 'Inter-State',
                $b['tax_rate'],
                $b['invoice_count'],
                number_format($b['taxable_value'], 2, '.', ''),
                number_format($b['cgst'], 2, '.', ''),
                number_format($b['sgst'], 2, '.', ''),
                number_format($b['igst'], 2, '.', ''),
                number_format($b['total_tax'], 2, '.', ''),
                number_format($b['total_value'], 2, '.', ''),
            ]);
        }
        fputcsv($output, []);

        // Table 12: HSN Summary
        fputcsv($output, ['--- TABLE 12: HSN-WISE SUMMARY OF OUTWARD SUPPLIES ---']);
        fputcsv($output, ['HSN / SAC Code', 'Description', 'UQC', 'Total Quantity', 'Taxable Value (₹)', 'Rate (%)', 'CGST (₹)', 'SGST (₹)', 'IGST (₹)', 'Total Tax (₹)', 'Total Value (₹)']);
        foreach ($gstr1['hsn_summary'] as $h) {
            fputcsv($output, [
                $h['hsn_code'],
                $h['description'],
                $h['uqc'],
                $h['total_qty'],
                number_format($h['taxable_value'], 2, '.', ''),
                $h['tax_rate'],
                number_format($h['cgst'], 2, '.', ''),
                number_format($h['sgst'], 2, '.', ''),
                number_format($h['igst'], 2, '.', ''),
                number_format($h['total_tax'], 2, '.', ''),
                number_format($h['total_value'], 2, '.', ''),
            ]);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }

    /**
     * Generate ITC Inward Supplies CSV formatted file content
     */
    public function exportItcCsv(Carbon $startDate, Carbon $endDate): string
    {
        $itc = $this->getItcData($startDate, $endDate);
        
        $output = fopen('php://temp', 'r+');

        fputcsv($output, ['--- INPUT TAX CREDIT (ITC) & INWARD PURCHASES LEDGER ---']);
        fputcsv($output, ['Filing Period', $itc['period']]);
        fputcsv($output, ['Total Taxable Inward Value (₹)', number_format($itc['grand_total_taxable'], 2, '.', '')]);
        fputcsv($output, ['Total Input CGST (₹)', number_format($itc['grand_total_cgst_itc'], 2, '.', '')]);
        fputcsv($output, ['Total Input SGST (₹)', number_format($itc['grand_total_sgst_itc'], 2, '.', '')]);
        fputcsv($output, ['Total Input IGST (₹)', number_format($itc['grand_total_igst_itc'], 2, '.', '')]);
        fputcsv($output, ['Grand Total ITC Claimable (₹)', number_format($itc['grand_total_itc'], 2, '.', '')]);
        fputcsv($output, []);

        // Purchase Orders Table
        fputcsv($output, ['--- VENDOR INWARD SUPPLIES (PURCHASE ORDERS) ---']);
        fputcsv($output, ['PO Number', 'Vendor Bill No', 'Bill Date', 'Supplier Name', 'Supplier GSTIN', 'Supplier State', 'Taxable Value (₹)', 'Rate (%)', 'CGST (₹)', 'SGST (₹)', 'IGST (₹)', 'ITC Amount (₹)', 'Bill Total (₹)']);
        foreach ($itc['purchase_orders'] as $po) {
            fputcsv($output, [
                $po['po_number'],
                $po['supplier_inv_no'],
                $po['supplier_inv_date'],
                $po['supplier_name'],
                $po['supplier_gstin'],
                $po['supplier_state'],
                number_format($po['taxable_value'], 2, '.', ''),
                $po['tax_rate'],
                number_format($po['cgst'], 2, '.', ''),
                number_format($po['sgst'], 2, '.', ''),
                number_format($po['igst'], 2, '.', ''),
                number_format($po['itc_amount'], 2, '.', ''),
                number_format($po['total_amount'], 2, '.', ''),
            ]);
        }
        fputcsv($output, []);

        // Expenses Table
        fputcsv($output, ['--- HARDWARE / MATERIALS EXPENSE CLAIMS (ITC ELIGIBLE) ---']);
        fputcsv($output, ['Claim No', 'Expense Date', 'Employee Name', 'Category', 'Description', 'Taxable Value (₹)', 'CGST (₹)', 'SGST (₹)', 'ITC Amount (₹)', 'Gross Payout (₹)']);
        foreach ($itc['expenses'] as $exp) {
            fputcsv($output, [
                $exp['claim_no'],
                $exp['expense_date'],
                $exp['employee_name'],
                $exp['category'],
                $exp['description'],
                number_format($exp['taxable_value'], 2, '.', ''),
                number_format($exp['cgst'], 2, '.', ''),
                number_format($exp['sgst'], 2, '.', ''),
                number_format($exp['itc_amount'], 2, '.', ''),
                number_format($exp['gross_amount'], 2, '.', ''),
            ]);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }
}
