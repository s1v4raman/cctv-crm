<?php

namespace App\Services;

use App\Models\DailyCashReconciliation;
use App\Models\InstallationJob;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PettyCashAccount;
use App\Models\PettyCashTransaction;
use App\Models\ServiceTicket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class PettyCashService
{
    /**
     * Get macro summary metrics of petty cash vault, field floats, and daily cash flow
     */
    public function getPettyCashSummary(?Carbon $date = null): array
    {
        $targetDate = $date ?: Carbon::now();
        $dateStr = $targetDate->toDateString();

        // 1. Ensure Main Vault exists
        $mainVault = PettyCashAccount::getMainVault();

        // 2. Aggregate all field wallets
        $fieldWallets = PettyCashAccount::where('account_type', 'field_wallet')
            ->where('is_active', true)
            ->get();

        $totalFieldFloat = (float) $fieldWallets->sum('current_balance');
        $totalVaultBalance = (float) $mainVault->current_balance;
        $totalLiquidCash = $totalVaultBalance + $totalFieldFloat;

        // 3. Count high float risk wallets (custodian holding > warning_limit)
        $highRiskWalletsCount = 0;
        foreach ($fieldWallets as $wallet) {
            if ($wallet->isHighFloatRisk()) {
                $highRiskWalletsCount++;
            }
        }

        // 4. Daily Inflow & Outflow transactions for the target date
        $dayTransactions = PettyCashTransaction::whereDate('transaction_date', $dateStr)->get();

        $dailyAdvances = (float) $dayTransactions->where('transaction_type', 'float_advance')->sum('amount');
        $dailyCollections = (float) $dayTransactions->where('transaction_type', 'field_collection')->sum('amount');
        $dailyExpenses = (float) $dayTransactions->where('transaction_type', 'direct_expense')->sum('amount');
        $dailyHandovers = (float) $dayTransactions->where('transaction_type', 'cash_deposit_handover')->sum('amount');

        // 5. Daily Reconciliations status
        $dayReconciliations = DailyCashReconciliation::whereDate('reconciliation_date', $dateStr)->get();
        $reconciledCount = $dayReconciliations->count();
        $matchedCount = $dayReconciliations->where('variance_status', 'matched')->count();
        $shortageCount = $dayReconciliations->where('variance_status', 'shortage')->count();
        $excessCount = $dayReconciliations->where('variance_status', 'excess')->count();
        $totalVariance = (float) $dayReconciliations->sum('variance_amount');

        return [
            'as_of_date'             => $targetDate->format('d M Y'),
            'total_liquid_cash'      => round($totalLiquidCash, 2),
            'main_vault_balance'     => round($totalVaultBalance, 2),
            'total_field_float'      => round($totalFieldFloat, 2),
            'active_custodians_count'=> $fieldWallets->count(),
            'high_risk_wallets_count'=> $highRiskWalletsCount,
            'daily_advances'         => round($dailyAdvances, 2),
            'daily_collections'      => round($dailyCollections, 2),
            'daily_expenses'         => round($dailyExpenses, 2),
            'daily_handovers'        => round($dailyHandovers, 2),
            'reconciled_count'       => $reconciledCount,
            'matched_count'          => $matchedCount,
            'shortage_count'         => $shortageCount,
            'excess_count'           => $excessCount,
            'total_variance'         => round($totalVariance, 2),
        ];
    }

    /**
     * Get all active accounts (Vault + Custodian field wallets) with latest activity
     */
    public function getAccountsList(array $filters = []): array
    {
        // Make sure Main Vault exists
        PettyCashAccount::getMainVault();

        $query = PettyCashAccount::with(['custodian', 'transactions' => fn($q) => $q->latest()->limit(1)]);

        if (!empty($filters['search'])) {
            $s = trim($filters['search']);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhereHas('custodian', function ($uq) use ($s) {
                      $uq->where('name', 'like', "%{$s}%")
                         ->orWhere('email', 'like', "%{$s}%")
                         ->orWhere('phone', 'like', "%{$s}%");
                  });
            });
        }

        if (!empty($filters['account_type'])) {
            $query->where('account_type', $filters['account_type']);
        }

        $accounts = $query->orderByRaw("CASE WHEN account_type = 'main_vault' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->get();

        $result = [];
        $today = Carbon::today()->toDateString();

        /** @var \App\Models\PettyCashAccount $acc */
        foreach ($accounts as $acc) {
            $latestTx = $acc->transactions->first();
            $latestReconciliation = DailyCashReconciliation::where('petty_cash_account_id', $acc->id)
                ->latest('reconciliation_date')
                ->first();

            $isReconciledToday = $latestReconciliation && Carbon::parse($latestReconciliation->reconciliation_date)->toDateString() === $today;

            $result[] = [
                'account'              => $acc,
                'id'                   => $acc->id,
                'name'                 => $acc->name,
                'account_type'         => $acc->account_type,
                'custodian'            => $acc->custodian,
                'custodian_name'       => $acc->custodian ? $acc->custodian->name : 'Office Cashier',
                'custodian_role'       => $acc->custodian ? ucfirst($acc->custodian->role) : 'Management',
                'current_balance'      => (float) $acc->current_balance,
                'warning_limit'        => (float) $acc->warning_limit,
                'is_high_risk'         => $acc->isHighFloatRisk(),
                'latest_tx_date'       => $latestTx?->transaction_date?->format('d M Y') ?? 'No Activity',
                'latest_tx_amount'     => $latestTx ? (float) $latestTx->amount : null,
                'latest_tx_type'       => $latestTx?->transaction_type,
                'latest_reconciliation'=> $latestReconciliation,
                'is_reconciled_today'  => $isReconciledToday,
            ];
        }

        return $result;
    }

    /**
     * Issue cash advance / top-up float from Main Vault to Field Wallet
     */
    public function recordFloatAdvance(array $data): PettyCashTransaction
    {
        return DB::transaction(function () use ($data) {
            $amount = (float) $data['amount'];
            if ($amount <= 0) {
                throw new InvalidArgumentException('Advance amount must be greater than zero.');
            }

            $mainVault = PettyCashAccount::getMainVault();
            if ((float) $mainVault->current_balance < $amount) {
                throw new InvalidArgumentException("Insufficient cash in Head Office Vault. Available: ₹" . number_format((float) $mainVault->current_balance, 2));
            }

            // Resolve target custodian wallet
            $targetUser = User::findOrFail($data['custodian_id']);
            $targetWallet = PettyCashAccount::getOrCreateWalletForUser($targetUser);

            $date = !empty($data['transaction_date']) ? Carbon::parse($data['transaction_date']) : Carbon::now();
            $voucherNo = PettyCashTransaction::generateVoucherNumber($date);

            // Create Transaction record
            $tx = PettyCashTransaction::create([
                'voucher_no'             => $voucherNo,
                'petty_cash_account_id'  => $mainVault->id,
                'destination_account_id' => $targetWallet->id,
                'user_id'                => Auth::id() ?: ($data['user_id'] ?? null),
                'transaction_type'       => 'float_advance',
                'amount'                 => $amount,
                'transaction_date'       => $date,
                'category'               => 'advance_float',
                'vendor_payee_name'      => $targetUser->name,
                'notes'                  => $data['notes'] ?? "Float advance issued to {$targetUser->name}",
                'status'                 => 'approved',
                'approved_by'            => Auth::id(),
                'approved_at'            => now(),
            ]);

            // Decrement Main Vault, Increment Field Wallet
            $mainVault->decrement('current_balance', $amount);
            $targetWallet->increment('current_balance', $amount);

            return $tx;
        });
    }

    /**
     * Record cash collected on-site by field technician from customer
     */
    public function recordFieldCollection(array $data): PettyCashTransaction
    {
        return DB::transaction(function () use ($data) {
            $amount = (float) $data['amount'];
            if ($amount <= 0) {
                throw new InvalidArgumentException('Collection amount must be greater than zero.');
            }

            // Resolve technician wallet
            $collectorId = $data['custodian_id'] ?? Auth::id();
            $user = User::findOrFail($collectorId);
            $wallet = PettyCashAccount::getOrCreateWalletForUser($user);

            $date = !empty($data['transaction_date']) ? Carbon::parse($data['transaction_date']) : Carbon::now();
            $voucherNo = PettyCashTransaction::generateVoucherNumber($date);

            $paymentId = null;
            // If linked to Invoice, also record a cash Payment record
            if (!empty($data['invoice_id'])) {
                $invoice = Invoice::find($data['invoice_id']);
                if ($invoice) {
                    $payment = Payment::create([
                        'receipt_no'     => Payment::generateReceiptNumber(),
                        'invoice_id'     => $invoice->id,
                        'quotation_id'   => $invoice->quotation_id,
                        'amount'         => $amount,
                        'paid_on'        => $date,
                        'method'         => 'cash',
                        'reference_no'   => $voucherNo,
                        'payment_status' => 'completed',
                        'notes'          => "Field cash collection recorded by {$user->name}",
                        'recorded_by'    => Auth::id(),
                    ]);
                    $paymentId = $payment->id;
                }
            }

            $tx = PettyCashTransaction::create([
                'voucher_no'             => $voucherNo,
                'petty_cash_account_id'  => $wallet->id,
                'user_id'                => Auth::id() ?: $user->id,
                'transaction_type'       => 'field_collection',
                'amount'                 => $amount,
                'transaction_date'       => $date,
                'category'               => 'customer_collection',
                'related_invoice_id'     => $data['invoice_id'] ?? null,
                'related_job_id'         => $data['job_id'] ?? null,
                'related_ticket_id'      => $data['ticket_id'] ?? null,
                'related_payment_id'     => $paymentId,
                'vendor_payee_name'      => $data['customer_name'] ?? 'Customer',
                'bill_receipt_no'        => $data['receipt_reference'] ?? null,
                'notes'                  => $data['notes'] ?? "On-site cash collection",
                'status'                 => 'approved',
                'approved_by'            => Auth::id(),
                'approved_at'            => now(),
            ]);

            // Increment Field Wallet balance
            $wallet->increment('current_balance', $amount);

            return $tx;
        });
    }

    /**
     * Record localized petty expense / site purchase spent from wallet
     */
    public function recordDirectExpense(array $data): PettyCashTransaction
    {
        return DB::transaction(function () use ($data) {
            $amount = (float) $data['amount'];
            if ($amount <= 0) {
                throw new InvalidArgumentException('Expense amount must be greater than zero.');
            }

            // Resolve wallet
            $account = !empty($data['petty_cash_account_id']) 
                ? PettyCashAccount::findOrFail($data['petty_cash_account_id'])
                : PettyCashAccount::getOrCreateWalletForUser(Auth::user());

            if ((float) $account->current_balance < $amount) {
                throw new InvalidArgumentException("Insufficient cash balance in {$account->name}. Available: ₹" . number_format((float) $account->current_balance, 2));
            }

            $date = !empty($data['transaction_date']) ? Carbon::parse($data['transaction_date']) : Carbon::now();
            $voucherNo = PettyCashTransaction::generateVoucherNumber($date);

            $receiptPath = null;
            if (!empty($data['receipt_photo']) && is_object($data['receipt_photo'])) {
                $receiptPath = $data['receipt_photo']->store('petty_cash_receipts', 'public');
            }

            $tx = PettyCashTransaction::create([
                'voucher_no'             => $voucherNo,
                'petty_cash_account_id'  => $account->id,
                'user_id'                => Auth::id(),
                'transaction_type'       => 'direct_expense',
                'amount'                 => $amount,
                'transaction_date'       => $date,
                'category'               => $data['category'] ?? 'hardware_conduits',
                'related_job_id'         => $data['job_id'] ?? null,
                'related_ticket_id'      => $data['ticket_id'] ?? null,
                'vendor_payee_name'      => $data['vendor_payee_name'] ?? 'Local Vendor',
                'bill_receipt_no'        => $data['bill_receipt_no'] ?? null,
                'receipt_photo_path'     => $receiptPath,
                'notes'                  => $data['notes'] ?? null,
                'status'                 => 'approved',
                'approved_by'            => Auth::id(),
                'approved_at'            => now(),
            ]);

            // Decrement wallet balance
            $account->decrement('current_balance', $amount);

            return $tx;
        });
    }

    /**
     * Record cash handover from field wallet back to Main Vault or Bank Deposit
     */
    public function recordCashHandover(array $data): PettyCashTransaction
    {
        return DB::transaction(function () use ($data) {
            $amount = (float) $data['amount'];
            if ($amount <= 0) {
                throw new InvalidArgumentException('Handover amount must be greater than zero.');
            }

            $sourceAccount = PettyCashAccount::findOrFail($data['source_account_id']);
            if ((float) $sourceAccount->current_balance < $amount) {
                throw new InvalidArgumentException("Cannot handover more than current balance in {$sourceAccount->name}. Available: ₹" . number_format((float) $sourceAccount->current_balance, 2));
            }

            $destinationType = $data['destination_type'] ?? 'main_vault'; // main_vault or bank_deposit
            $destinationAccount = null;

            if ($destinationType === 'main_vault') {
                $destinationAccount = PettyCashAccount::getMainVault();
            }

            $date = !empty($data['transaction_date']) ? Carbon::parse($data['transaction_date']) : Carbon::now();
            $voucherNo = PettyCashTransaction::generateVoucherNumber($date);

            $tx = PettyCashTransaction::create([
                'voucher_no'             => $voucherNo,
                'petty_cash_account_id'  => $sourceAccount->id,
                'destination_account_id' => $destinationAccount?->id,
                'user_id'                => Auth::id(),
                'transaction_type'       => 'cash_deposit_handover',
                'amount'                 => $amount,
                'transaction_date'       => $date,
                'category'               => $destinationType === 'bank_deposit' ? 'bank_deposit' : 'advance_float',
                'vendor_payee_name'      => $destinationType === 'bank_deposit' ? ($data['bank_name'] ?? 'Bank Deposit') : 'Head Office Safe',
                'bill_receipt_no'        => $data['bank_ack_no'] ?? null,
                'notes'                  => $data['notes'] ?? ($destinationType === 'bank_deposit' ? "Deposited to Bank" : "Handover to Main Vault"),
                'status'                 => 'approved',
                'approved_by'            => Auth::id(),
                'approved_at'            => now(),
            ]);

            // Decrement source wallet
            $sourceAccount->decrement('current_balance', $amount);

            // If handed over to Main Vault, increment vault
            if ($destinationAccount) {
                $destinationAccount->increment('current_balance', $amount);
            }

            return $tx;
        });
    }

    /**
     * Record End-of-Day Physical Cash Reconciliation with Denominations Tally
     */
    public function recordDailyReconciliation(array $data): DailyCashReconciliation
    {
        return DB::transaction(function () use ($data) {
            $account = PettyCashAccount::findOrFail($data['petty_cash_account_id']);
            $date = !empty($data['reconciliation_date']) ? Carbon::parse($data['reconciliation_date']) : Carbon::today();
            $dateStr = $date->toDateString();

            $denominations = $data['denominations'] ?? [];
            $physicalCount = DailyCashReconciliation::calculateDenominationTotal($denominations);

            // Compute transactions for this account on the date
            $inflows = (float) PettyCashTransaction::where(function ($q) use ($account) {
                    $q->where('petty_cash_account_id', $account->id)
                      ->where('transaction_type', 'field_collection');
                })
                ->orWhere(function ($q) use ($account) {
                    $q->where('destination_account_id', $account->id)
                      ->where('transaction_type', 'float_advance');
                })
                ->whereDate('transaction_date', $dateStr)
                ->sum('amount');

            $outflows = (float) PettyCashTransaction::where('petty_cash_account_id', $account->id)
                ->whereIn('transaction_type', ['direct_expense', 'cash_deposit_handover'])
                ->whereDate('transaction_date', $dateStr)
                ->sum('amount');

            $systemExpected = (float) $account->current_balance;
            $variance = round($physicalCount - $systemExpected, 2);

            $varianceStatus = 'matched';
            if ($variance < -0.01) {
                $varianceStatus = 'shortage';
            } elseif ($variance > 0.01) {
                $varianceStatus = 'excess';
            }

            $recNo = DailyCashReconciliation::generateReconciliationNumber($date);

            return DailyCashReconciliation::create([
                'reconciliation_no'        => $recNo,
                'petty_cash_account_id'    => $account->id,
                'custodian_id'             => $account->custodian_id ?: Auth::id(),
                'reconciliation_date'      => $date,
                'opening_balance'          => max(0.0, $systemExpected - $inflows + $outflows),
                'total_inflow'             => $inflows,
                'total_outflow'            => $outflows,
                'system_expected_balance'  => $systemExpected,
                'physical_counted_balance' => $physicalCount,
                'variance_amount'          => $variance,
                'variance_status'          => $varianceStatus,
                'denominations'            => $denominations,
                'reconciliation_notes'     => $data['reconciliation_notes'] ?? null,
                'verified_by'              => Auth::id(),
                'verified_at'              => now(),
                'status'                   => 'verified',
            ]);
        });
    }

    /**
     * Get Chronological Debit/Credit statement for a specific wallet or vault
     */
    public function getAccountLedger(PettyCashAccount $account, array $filters = []): array
    {
        $query = PettyCashTransaction::with(['user', 'job', 'ticket', 'invoice', 'destinationAccount'])
            ->where(function ($q) use ($account) {
                $q->where('petty_cash_account_id', $account->id)
                  ->orWhere('destination_account_id', $account->id);
            })
            ->orderBy('transaction_date')
            ->orderBy('id');

        if (!empty($filters['from_date'])) {
            $query->whereDate('transaction_date', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('transaction_date', '<=', $filters['to_date']);
        }

        $transactions = $query->get();
        $ledgerEntries = [];
        $runningBalance = 0.0;
        $totalIn = 0.0;
        $totalOut = 0.0;

        foreach ($transactions as $tx) {
            $isDestination = $tx->destination_account_id == $account->id;
            $isInflow = false;
            $isOutflow = false;
            $amount = (float) $tx->amount;

            if ($account->account_type === 'main_vault') {
                if ($tx->transaction_type === 'float_advance') {
                    $isOutflow = true;
                } elseif ($tx->transaction_type === 'cash_deposit_handover' && $isDestination) {
                    $isInflow = true;
                } elseif ($tx->transaction_type === 'field_collection') {
                    $isInflow = true;
                } elseif ($tx->transaction_type === 'direct_expense') {
                    $isOutflow = true;
                }
            } else { // field_wallet
                if ($tx->transaction_type === 'float_advance' && $isDestination) {
                    $isInflow = true;
                } elseif ($tx->transaction_type === 'field_collection') {
                    $isInflow = true;
                } elseif ($tx->transaction_type === 'direct_expense') {
                    $isOutflow = true;
                } elseif ($tx->transaction_type === 'cash_deposit_handover') {
                    $isOutflow = true;
                }
            }

            if ($isInflow) {
                $runningBalance += $amount;
                $totalIn += $amount;
                $debit = 0.0;
                $credit = $amount;
            } else {
                $runningBalance -= $amount;
                $totalOut += $amount;
                $debit = $amount;
                $credit = 0.0;
            }

            $ledgerEntries[] = [
                'transaction'     => $tx,
                'voucher_no'      => $tx->voucher_no,
                'date'            => $tx->transaction_date->format('d M Y'),
                'type'            => $tx->transaction_type,
                'category'        => ucfirst(str_replace('_', ' ', $tx->category ?? $tx->transaction_type)),
                'payee_payer'     => $tx->vendor_payee_name ?: ($tx->user?->name ?? 'System'),
                'notes'           => $tx->notes,
                'receipt_no'      => $tx->bill_receipt_no,
                'has_receipt_doc' => !empty($tx->receipt_photo_path),
                'inflow'          => $credit,
                'outflow'         => $debit,
                'balance'         => round($runningBalance, 2),
            ];
        }

        return [
            'account'         => $account,
            'entries'         => array_reverse($ledgerEntries), // latest first for UI
            'total_inflow'    => round($totalIn, 2),
            'total_outflow'   => round($totalOut, 2),
            'current_balance' => round((float) $account->current_balance, 2),
        ];
    }

    /**
     * Export Cashbook CSV Spreadsheet
     */
    public function exportCashbookCsv(array $filters = []): string
    {
        $summary = $this->getPettyCashSummary();
        $transactions = PettyCashTransaction::with(['account', 'destinationAccount', 'user'])->latest('transaction_date')->get();

        $output = fopen('php://temp', 'r+');

        fputcsv($output, ['--- COMPANY PETTY CASH & DAILY CASHBOOK REPORT ---']);
        fputcsv($output, ['Generated Date', now()->format('d M Y, h:i A')]);
        fputcsv($output, ['Main Vault Balance (INR)', $summary['main_vault_balance']]);
        fputcsv($output, ['Total Field Floats (INR)', $summary['total_field_float']]);
        fputcsv($output, ['Total Liquid Cash (INR)', $summary['total_liquid_cash']]);
        fputcsv($output, ['Day Collections (INR)', $summary['daily_collections']]);
        fputcsv($output, ['Day Disbursements (INR)', $summary['daily_expenses']]);
        fputcsv($output, []);

        fputcsv($output, [
            'Voucher #',
            'Date',
            'Account / Custodian',
            'Transaction Type',
            'Category',
            'Payee / Payer',
            'Amount (INR)',
            'Receipt Reference',
            'Destination Account',
            'Recorded By',
            'Status',
            'Notes',
        ]);

        foreach ($transactions as $tx) {
            fputcsv($output, [
                $tx->voucher_no,
                $tx->transaction_date ? Carbon::parse($tx->transaction_date)->format('Y-m-d') : '',
                $tx->account?->name ?? 'Vault',
                ucfirst(str_replace('_', ' ', $tx->transaction_type)),
                ucfirst(str_replace('_', ' ', $tx->category ?? 'General')),
                $tx->vendor_payee_name ?? 'N/A',
                $tx->amount,
                $tx->bill_receipt_no ?? '-',
                $tx->destinationAccount?->name ?? '-',
                $tx->user?->name ?? 'System',
                ucfirst($tx->status),
                $tx->notes ?? '',
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
