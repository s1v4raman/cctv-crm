<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use App\Models\WagePayment;
use App\Models\ProjectWorkerAttendance;
use App\Models\ProjectAuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * Controller for managing worker weekly wages, payouts, and advances.
 */
class WageController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            function ($request, $next) {
                if (!Auth::user() || !Auth::user()->isAdmin()) {
                    abort(403, 'Only administrators can view the weekly wages sheet, record advances, or mark wages paid.');
                }
                return $next($request);
            },
        ];
    }

    /**
     * Sunday Payday Weekly Wage Console.
     */
    public function index(Request $request)
    {
        // Default to current week's Monday (or query param 'week')
        $weekParam = $request->input('week');
        if ($weekParam) {
            $weekStart = Carbon::parse($weekParam)->startOfWeek();
        } else {
            $weekStart = Carbon::now()->startOfWeek();
        }
        $weekEnd = (clone $weekStart)->endOfWeek(); // covers Monday to Sunday
        $saturdayEnd = (clone $weekStart)->addDays(5)->endOfDay(); // Saturday 23:59:59
        $paydaySunday = (clone $weekStart)->addDays(6);

        $workers = Worker::where('is_active', true)
            ->orderBy('name')
            ->get();

        $wageRows = [];
        $totalDaysAll = 0;
        $totalMetresAll = 0;
        $totalCamerasAll = 0;
        $totalEarnedAll = 0;
        $totalBroughtForwardAll = 0;
        $totalAdvancesAll = 0;
        $totalWagesPaidAll = 0;
        $totalDueAll = 0;

        foreach ($workers as $worker) {
            $broughtForward = $worker->getBroughtForward($weekStart->toDateString());
            $earned = $worker->getEarnedInWeek($weekStart->toDateString(), $weekEnd->toDateString());
            $advances = $worker->getAdvancesInWeek($weekStart->toDateString(), $weekEnd->toDateString());
            $grossPayable = max(0, $broughtForward + $earned - $advances);
            $wagesPaid = $worker->getWagesPaidForWeek($weekStart->toDateString(), $weekEnd->toDateString());

            // Check if worker has an explicit carry forward recorded for this cycle
            $weekWageRecords = $worker->wagePayments()
                ->where('payment_type', 'wage')
                ->where(function ($q) use ($weekStart, $weekEnd) {
                    $q->where(function ($sq) use ($weekStart) {
                        $sq->where('week_start', $weekStart->toDateString())
                           ->orWhere('week_start_date', $weekStart->toDateString());
                    })->orWhere(function ($sq) use ($weekStart, $weekEnd) {
                        $sq->whereNull('week_start')
                           ->whereNull('week_start_date')
                           ->whereBetween('payment_date', [$weekStart->toDateString(), (clone $weekEnd)->addDay()->toDateString()]);
                    });
                })
                ->get();

            $carryFwdRecord = $weekWageRecords->first(function ($p) {
                return $p->amount == 0 && (str_contains(strtolower($p->reference_notes ?? ''), 'carried forward') || str_contains(strtolower($p->reference_notes ?? ''), 'carry_forward'));
            });

            $isCarriedForward = false;
            $carriedForwardBalance = 0;
            if ($carryFwdRecord && ($wagesPaid < $grossPayable)) {
                $isCarriedForward = true;
                $carriedForwardBalance = max(0, $grossPayable - $wagesPaid);
            }

            // Net due to disburse on Sunday
            if ($isCarriedForward) {
                $totalDue = 0; // Balance deferred to next week
            } else {
                $totalDue = max(0, $grossPayable - $wagesPaid);
            }

            // Fetch attendances in this week for this worker (Monday through Sunday)
            $attendances = ProjectWorkerAttendance::where('worker_id', $worker->id)
                ->whereHas('workDay', function ($q) use ($weekStart, $weekEnd) {
                    $q->whereBetween('work_date', [$weekStart->toDateString(), $weekEnd->toDateString()]);
                })
                ->with(['workDay.project', 'wagePayment'])
                ->orderByDesc('id')
                ->get();

            // Fetch advances taken by this worker in this week
            $workerAdvances = $worker->wagePayments()
                ->where('payment_type', 'advance')
                ->where(function ($q) use ($weekStart, $weekEnd) {
                    $q->where(function ($sq) use ($weekStart) {
                        $sq->where('week_start', $weekStart->toDateString())
                           ->orWhere('week_start_date', $weekStart->toDateString());
                    })->orWhere(function ($sq) use ($weekStart, $weekEnd) {
                        $sq->whereNull('week_start')
                           ->whereNull('week_start_date')
                           ->whereBetween('payment_date', [$weekStart->toDateString(), (clone $weekEnd)->toDateString()]);
                    });
                })
                ->orderByDesc('payment_date')
                ->get();

            // Fetch wage payments made to this worker in this week
            $workerPayouts = $worker->wagePayments()
                ->where('payment_type', 'wage')
                ->where(function ($q) use ($weekStart, $weekEnd) {
                    $q->where(function ($sq) use ($weekStart) {
                        $sq->where('week_start', $weekStart->toDateString())
                           ->orWhere('week_start_date', $weekStart->toDateString());
                    })->orWhere(function ($sq) use ($weekStart, $weekEnd) {
                        $sq->whereNull('week_start')
                           ->whereNull('week_start_date')
                           ->whereBetween('payment_date', [$weekStart->toDateString(), (clone $weekEnd)->addDay()->toDateString()]);
                    });
                })
                ->orderByDesc('payment_date')
                ->get();

            // Prior unpaid shifts contributing to Brought Forward
            $priorUnpaidAttendances = ProjectWorkerAttendance::where('worker_id', $worker->id)
                ->whereHas('workDay', function ($q) use ($weekStart) {
                    $q->where('work_date', '<', $weekStart->toDateString());
                })
                ->where('is_paid', false)
                ->with(['workDay.project', 'wagePayment'])
                ->orderByDesc('id')
                ->get();

            $priorDaysWorked = $priorUnpaidAttendances->count();
            $priorMetres = (float) $priorUnpaidAttendances->sum('cabling_metres');
            $priorCameras = (int) $priorUnpaidAttendances->sum('cameras_installed');

            $daysWorked = $attendances->count();
            $totalMetres = (float) $attendances->sum('cabling_metres');
            $totalCameras = (int) $attendances->sum('cameras_installed');
            $alreadySettled = ($grossPayable > 0 && $totalDue <= 0) || ($attendances->isNotEmpty() && $attendances->every(fn($a) => $a->is_paid));

            $wageRows[] = [
                'worker' => $worker,
                'brought_forward' => $broughtForward,
                'earned' => $earned,
                'advances' => $advances,
                'gross_payable' => $grossPayable,
                'wages_paid' => $wagesPaid,
                'total_due' => $totalDue,
                'is_carried_forward' => $isCarriedForward,
                'carried_forward_balance' => $carriedForwardBalance,
                'days_worked' => $daysWorked,
                'total_metres' => $totalMetres,
                'total_cameras' => $totalCameras,
                'prior_days_worked' => $priorDaysWorked,
                'prior_metres' => $priorMetres,
                'prior_cameras' => $priorCameras,
                'prior_attendances' => $priorUnpaidAttendances,
                'already_settled' => $alreadySettled,
                'attendances' => $attendances,
                'worker_advances' => $workerAdvances,
                'worker_payouts' => $workerPayouts,
            ];

            $totalDaysAll += $daysWorked;
            $totalMetresAll += $totalMetres;
            $totalCamerasAll += $totalCameras;
            $totalBroughtForwardAll += $broughtForward;
            $totalEarnedAll += $earned;
            $totalAdvancesAll += $advances;
            $totalWagesPaidAll += $wagesPaid;
            $totalDueAll += $totalDue;
        }

        // Recent wage transactions for this week
        $recentPayments = WagePayment::where(function ($q) use ($weekStart, $weekEnd) {
                $q->where(function ($sq) use ($weekStart) {
                    $sq->where('week_start', $weekStart->toDateString())
                       ->orWhere('week_start_date', $weekStart->toDateString());
                })->orWhere(function ($sq) use ($weekStart, $weekEnd) {
                    $sq->whereNull('week_start')
                       ->whereNull('week_start_date')
                       ->whereBetween('payment_date', [$weekStart->toDateString(), (clone $weekEnd)->addDay()->toDateString()]);
                });
            })
            ->with(['worker', 'settledBy'])
            ->orderByDesc('id')
            ->get();

        // Available weeks for dropdown (last 12 weeks)
        $availableWeeks = [];
        $curr = Carbon::now()->startOfWeek();
        for ($i = 0; $i < 12; $i++) {
            $wStart = (clone $curr)->subWeeks($i);
            $wSun = (clone $wStart)->addDays(6);
            $availableWeeks[] = [
                'val' => $wStart->toDateString(),
                'label' => $wStart->format('d M') . ' – ' . $wSun->format('d M, Y') . ' (Payday ' . $wSun->format('D') . ')',
            ];
        }

        return view('wages.index', compact(
            'wageRows',
            'weekStart',
            'weekEnd',
            'paydaySunday',
            'availableWeeks',
            'recentPayments',
            'totalDaysAll',
            'totalMetresAll',
            'totalCamerasAll',
            'totalBroughtForwardAll',
            'totalEarnedAll',
            'totalAdvancesAll',
            'totalWagesPaidAll',
            'totalDueAll'
        ));
    }

    /**
     * Record an advance payment to a worker.
     */
    public function recordAdvance(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:workers,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|in:cash,upi,bank_transfer,cheque',
            'reference_notes' => 'nullable|string|max:255',
        ]);

        $worker = Worker::findOrFail($validated['worker_id']);
        $paymentDate = Carbon::parse($validated['payment_date']);
        $weekStart = (clone $paymentDate)->startOfWeek()->toDateString();
        $weekEnd = (clone $paymentDate)->endOfWeek()->toDateString();

        $payment = WagePayment::create([
            'worker_id' => $worker->id,
            'payment_type' => 'advance',
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'week_start_date' => $weekStart,
            'week_end_date' => $weekEnd,
            'payment_mode' => $validated['payment_mode'],
            'reference_notes' => $validated['reference_notes'] ?? 'Mid-week advance payout',
            'notes' => $validated['reference_notes'] ?? 'Mid-week advance payout',
            'settled_by' => Auth::id(),
            'paid_by' => Auth::id(),
        ]);

        ProjectAuditLog::logChange(
            $worker,
            'advance_issued',
            null,
            null,
            "Advance of ₹{$payment->amount} paid to {$worker->name} via " . strtoupper($payment->payment_mode)
        );

        return back()->with('status', "Advance of ₹" . number_format($payment->amount, 2) . " recorded for {$worker->name}.");
    }

    /**
     * Settle Sunday wage payment (Pay Full, Pay Part, Carry Forward).
     */
    public function settlePayment(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:workers,id',
            'settlement_action' => 'required|in:pay_full,pay_part,carry_forward',
            'amount_to_pay' => 'nullable|numeric|min:0',
            'week_start' => 'required|date',
            'payment_mode' => 'required|in:cash,upi,bank_transfer,cheque',
            'reference_notes' => 'nullable|string|max:255',
        ]);

        $worker = Worker::findOrFail($validated['worker_id']);
        $weekStart = Carbon::parse($validated['week_start'])->startOfWeek()->toDateString();
        $weekEnd = Carbon::parse($weekStart)->endOfWeek()->toDateString();

        $broughtForward = $worker->getBroughtForward($weekStart);
        $earned = $worker->getEarnedInWeek($weekStart, $weekEnd);
        $advances = $worker->getAdvancesInWeek($weekStart, $weekEnd);
        $grossPayable = max(0, $broughtForward + $earned - $advances);
        $wagesPaidSoFar = $worker->getWagesPaidForWeek($weekStart, $weekEnd);
        $remainingDue = max(0, $grossPayable - $wagesPaidSoFar);

        $action = $validated['settlement_action'];
        $amountPaid = 0;

        if ($action === 'pay_full') {
            $amountPaid = $remainingDue;
        } elseif ($action === 'pay_part') {
            $requested = (float) ($validated['amount_to_pay'] ?? 0);
            $amountPaid = min($requested, $remainingDue);
        } elseif ($action === 'carry_forward') {
            $amountPaid = 0;
        }

        DB::beginTransaction();
        try {
            $notes = $validated['reference_notes'] ?? null;
            if ($action === 'carry_forward') {
                $notes = $notes ?: "Carried forward to next week (Balance: ₹" . number_format($remainingDue, 2) . ")";
            } elseif ($action === 'pay_full') {
                $notes = $notes ?: "Full wage payout of ₹" . number_format($amountPaid, 2);
            } elseif ($action === 'pay_part') {
                $rem = max(0, $remainingDue - $amountPaid);
                $notes = $notes ?: "Partial wage payout of ₹" . number_format($amountPaid, 2) . " (Remaining: ₹" . number_format($rem, 2) . ")";
            }

            // Always create a payment/settlement record so the action is captured and recorded
            $payment = WagePayment::create([
                'worker_id' => $worker->id,
                'payment_type' => 'wage',
                'amount' => $amountPaid,
                'payment_date' => now()->toDateString(),
                'week_start' => $weekStart,
                'week_end' => $weekEnd,
                'week_start_date' => $weekStart,
                'week_end_date' => $weekEnd,
                'payment_mode' => $validated['payment_mode'] ?? 'cash',
                'reference_notes' => $notes,
                'notes' => $notes,
                'settled_by' => Auth::id(),
                'paid_by' => Auth::id(),
            ]);

            // Lock attendance records for this week (and prior unpaid attendances if paid in full)
            if ($action === 'pay_full') {
                $attendances = ProjectWorkerAttendance::where('worker_id', $worker->id)
                    ->where('is_paid', false)
                    ->whereHas('workDay', function ($q) use ($weekEnd) {
                        $q->where('work_date', '<=', $weekEnd);
                    })
                    ->get();
            } else {
                $attendances = ProjectWorkerAttendance::where('worker_id', $worker->id)
                    ->whereHas('workDay', function ($q) use ($weekStart, $weekEnd) {
                        $q->whereBetween('work_date', [$weekStart, $weekEnd]);
                    })
                    ->get();
            }

            foreach ($attendances as $att) {
                $att->update([
                    'is_paid' => true,
                    'paid_week_start' => $weekStart,
                    'wage_payment_id' => $payment->id,
                ]);
            }

            ProjectAuditLog::logChange(
                $worker,
                'wage_settled',
                null,
                null,
                "Sunday settlement for {$worker->name}: action {$action}, paid ₹{$amountPaid}, attendances locked"
            );

            DB::commit();

            $msg = match($action) {
                'carry_forward' => "Balance of ₹" . number_format($remainingDue, 2) . " for {$worker->name} has been carried forward to next week.",
                'pay_part' => "Partial wage payout of ₹" . number_format($amountPaid, 2) . " recorded for {$worker->name}. Remaining balance: ₹" . number_format(max(0, $remainingDue - $amountPaid), 2) . ".",
                default => "Full wage settlement of ₹" . number_format($amountPaid, 2) . " completed for {$worker->name}.",
            };

            return back()->with('status', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to settle wages: ' . $e->getMessage());
        }
    }

    /**
     * Admin 1-click single-shift daily wage payout from Weekly Wages console.
     */
    public function payAttendanceShift(Request $request, ProjectWorkerAttendance $attendance)
    {
        if ($attendance->is_paid) {
            return back()->with('error', 'This work shift is already marked as paid.');
        }

        $validated = $request->validate([
            'payment_mode' => 'nullable|string|in:cash,upi,bank_transfer,cheque',
            'notes' => 'nullable|string|max:255',
        ]);

        $worker = $attendance->worker;
        $workDate = Carbon::parse($attendance->workDay->work_date);
        $weekStart = (clone $workDate)->startOfWeek()->toDateString();
        $weekEnd = (clone $workDate)->endOfWeek()->toDateString();
        $amount = (float) $attendance->total_amount;
        $mode = $validated['payment_mode'] ?? 'cash';
        $notes = $validated['notes'] ?? "Per-day daily wage payout for {$workDate->format('d M Y')} ({$attendance->workDay->project->project_code})";

        DB::beginTransaction();
        try {
            $payment = WagePayment::create([
                'worker_id' => $worker->id,
                'payment_type' => 'wage',
                'amount' => $amount,
                'payment_date' => now()->toDateString(),
                'week_start' => $weekStart,
                'week_end' => $weekEnd,
                'week_start_date' => $weekStart,
                'week_end_date' => $weekEnd,
                'payment_mode' => $mode,
                'reference_notes' => $notes,
                'notes' => $notes,
                'settled_by' => Auth::id(),
                'paid_by' => Auth::id(),
            ]);

            $attendance->update([
                'is_paid' => true,
                'is_locked' => true,
                'paid_week_start' => $weekStart,
                'wage_payment_id' => $payment->id,
            ]);

            ProjectAuditLog::logChange(
                $attendance->workDay->project,
                'shift_wage_paid',
                null,
                null,
                "Daily wage of ₹" . number_format($amount, 2) . " paid to {$worker->name} for shift on {$workDate->format('d M Y')}"
            );

            DB::commit();

            return back()->with('status', "✓ Daily wage payout of ₹" . number_format($amount, 2) . " paid to {$worker->name} for {$workDate->format('d M Y')}!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process payout: ' . $e->getMessage());
        }
    }

    /**
     * Printable Sunday Cash Signature Sheet.
     */
    public function printSignatureSheet(Request $request)
    {
        $weekParam = $request->input('week');
        $weekStart = $weekParam ? Carbon::parse($weekParam)->startOfWeek() : Carbon::now()->startOfWeek();
        $weekEnd = (clone $weekStart)->endOfWeek();
        $saturdayEnd = (clone $weekStart)->addDays(5)->endOfDay();
        $paydaySunday = (clone $weekStart)->addDays(6);

        $workers = Worker::where('is_active', true)->orderBy('name')->get();

        $rows = [];
        $totalCash = 0;

        foreach ($workers as $worker) {
            $broughtForward = $worker->getBroughtForward($weekStart->toDateString());
            $earned = $worker->getEarnedInWeek($weekStart->toDateString(), $weekEnd->toDateString());
            $advances = $worker->getAdvancesInWeek($weekStart->toDateString(), $paydaySunday->toDateString());
            $grossPayable = max(0, $broughtForward + $earned - $advances);
            $wagesPaid = $worker->getWagesPaidForWeek($weekStart->toDateString(), $paydaySunday->toDateString());

            $isCarriedForward = $worker->wagePayments()
                ->where('payment_type', 'wage')
                ->where('amount', 0)
                ->where(function ($q) use ($weekStart) {
                    $q->where('week_start', $weekStart->toDateString())
                       ->orWhere('week_start_date', $weekStart->toDateString());
                })
                ->where(function ($q) {
                    $q->where('reference_notes', 'like', '%carried forward%')
                      ->orWhere('reference_notes', 'like', '%carry_forward%');
                })
                ->exists();

            $remainingDue = ($isCarriedForward) ? 0 : max(0, $grossPayable - $wagesPaid);

            $attendances = ProjectWorkerAttendance::where('worker_id', $worker->id)
                ->whereHas('workDay', function ($q) use ($weekStart, $weekEnd) {
                    $q->whereBetween('work_date', [$weekStart->toDateString(), $weekEnd->toDateString()]);
                })
                ->get();

            if ($remainingDue > 0 || $attendances->count() > 0 || $wagesPaid > 0) {
                $rows[] = [
                    'worker' => $worker,
                    'days_worked' => $attendances->count(),
                    'metres' => (float) $attendances->sum('cabling_metres'),
                    'cameras' => (int) $attendances->sum('cameras_installed'),
                    'brought_forward' => $broughtForward,
                    'earned' => $earned,
                    'advances' => $advances,
                    'wages_paid' => $wagesPaid,
                    'total_due' => $remainingDue,
                    'is_carried_forward' => $isCarriedForward,
                ];
                $totalCash += $remainingDue;
            }
        }

        return view('wages.signature_sheet', compact('rows', 'weekStart', 'paydaySunday', 'totalCash'));
    }

    /**
     * Export weekly wage sheet to CSV.
     */
    public function exportCsv(Request $request)
    {
        $weekParam = $request->input('week');
        $weekStart = $weekParam ? Carbon::parse($weekParam)->startOfWeek() : Carbon::now()->startOfWeek();
        $weekEnd = (clone $weekStart)->endOfWeek();
        $saturdayEnd = (clone $weekStart)->addDays(5)->endOfDay();
        $paydaySunday = (clone $weekStart)->addDays(6);

        $workers = Worker::where('is_active', true)->orderBy('name')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"Weekly_Wages_{$weekStart->format('Y-m-d')}.csv\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return new StreamedResponse(function () use ($workers, $weekStart, $weekEnd, $paydaySunday) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['SecureVision CRM - Sunday Weekly Wage Payout Ledger']);
            fputcsv($handle, ['Week Cycle', $weekStart->format('d M Y') . ' to ' . $weekEnd->format('d M Y')]);
            fputcsv($handle, ['Payday Date', $paydaySunday->format('d M Y')]);
            fputcsv($handle, ['Generated At', now()->toDateTimeString()]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'Worker Name',
                'Phone',
                'Daily Rate (₹)',
                'Days Worked',
                'Cabling Metres',
                'Cameras Installed',
                'Brought Forward (₹)',
                'Earned This Week (₹)',
                'Advances Taken (₹)',
                'Paid This Week (₹)',
                'Total Payable (₹)',
                'Status'
            ]);

            foreach ($workers as $w) {
                $bf = $w->getBroughtForward($weekStart->toDateString());
                $earned = $w->getEarnedInWeek($weekStart->toDateString(), $weekEnd->toDateString());
                $adv = $w->getAdvancesInWeek($weekStart->toDateString(), $paydaySunday->toDateString());
                $gross = max(0, $bf + $earned - $adv);
                $wagesPaid = $w->getWagesPaidForWeek($weekStart->toDateString(), $paydaySunday->toDateString());

                $isCf = $w->wagePayments()
                    ->where('payment_type', 'wage')
                    ->where('amount', 0)
                    ->where(function ($q) use ($weekStart) {
                        $q->where('week_start', $weekStart->toDateString())
                           ->orWhere('week_start_date', $weekStart->toDateString());
                    })
                    ->where(function ($q) {
                        $q->where('reference_notes', 'like', '%carried forward%')
                          ->orWhere('reference_notes', 'like', '%carry_forward%');
                    })
                    ->exists();

                $due = ($isCf) ? 0 : max(0, $gross - $wagesPaid);

                $attQuery = ProjectWorkerAttendance::where('worker_id', $w->id)
                    ->whereHas('workDay', fn($q) => $q->whereBetween('work_date', [$weekStart->toDateString(), $weekEnd->toDateString()]));

                $attCount = (clone $attQuery)->count();
                $metres = (float) (clone $attQuery)->sum('cabling_metres');
                $cameras = (int) (clone $attQuery)->sum('cameras_installed');

                $status = 'Due for Settlement';
                if ($isCf) {
                    $status = 'Carried Forward';
                } elseif ($due <= 0 && ($wagesPaid > 0 || $gross == 0)) {
                    $status = 'Settled';
                } elseif ($wagesPaid > 0) {
                    $status = 'Partially Settled';
                }

                fputcsv($handle, [
                    $w->name,
                    $w->phone ?? '—',
                    number_format($w->daily_rate, 2),
                    $attCount,
                    $metres,
                    $cameras,
                    number_format($bf, 2),
                    number_format($earned, 2),
                    number_format($adv, 2),
                    number_format($wagesPaid, 2),
                    number_format($due, 2),
                    $status
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
