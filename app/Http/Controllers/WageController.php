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
            $weekStart = Carbon::parse($weekParam)->startOfWeek(Carbon::MONDAY);
        } else {
            $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        }
        $weekEnd = (clone $weekStart)->endOfWeek(Carbon::SUNDAY); // covers Monday to Sunday
        $saturdayEnd = (clone $weekStart)->addDays(5)->endOfDay(); // Saturday 23:59:59
        $paydaySunday = (clone $weekStart)->addDays(6);

        $workers = Worker::where('is_active', true)
            ->orderBy('name')
            ->get();

        $wageRows = [];
        $totalEarnedAll = 0;
        $totalBroughtForwardAll = 0;
        $totalAdvancesAll = 0;
        $totalDueAll = 0;

        foreach ($workers as $worker) {
            $broughtForward = $worker->getBroughtForward($weekStart->toDateString());
            $earned = $worker->getEarnedInWeek($weekStart->toDateString(), $saturdayEnd->toDateString());
            $advances = $worker->getAdvancesInWeek($weekStart->toDateString(), $weekEnd->toDateString());
            $totalDue = max(0, $broughtForward + $earned - $advances);

            // Fetch attendances in this week for this worker
            $attendances = ProjectWorkerAttendance::where('worker_id', $worker->id)
                ->whereHas('workDay', function ($q) use ($weekStart, $saturdayEnd) {
                    $q->whereBetween('work_date', [$weekStart->toDateString(), $saturdayEnd->toDateString()]);
                })
                ->with('workDay.project')
                ->get();

            $daysWorked = $attendances->count();
            $totalMetres = $attendances->sum('cabling_metres');
            $alreadySettled = $attendances->isNotEmpty() && $attendances->every(fn($a) => $a->is_paid);

            $wageRows[] = [
                'worker' => $worker,
                'brought_forward' => $broughtForward,
                'earned' => $earned,
                'advances' => $advances,
                'total_due' => $totalDue,
                'days_worked' => $daysWorked,
                'total_metres' => $totalMetres,
                'already_settled' => $alreadySettled,
                'attendances' => $attendances,
            ];

            $totalBroughtForwardAll += $broughtForward;
            $totalEarnedAll += $earned;
            $totalAdvancesAll += $advances;
            $totalDueAll += $totalDue;
        }

        // Available weeks for dropdown (last 12 weeks)
        $availableWeeks = [];
        $curr = Carbon::now()->startOfWeek(Carbon::MONDAY);
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
            'totalBroughtForwardAll',
            'totalEarnedAll',
            'totalAdvancesAll',
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
        $weekStart = (clone $paymentDate)->startOfWeek(Carbon::MONDAY)->toDateString();
        $weekEnd = (clone $paymentDate)->endOfWeek(Carbon::SUNDAY)->toDateString();

        $payment = WagePayment::create([
            'worker_id' => $worker->id,
            'payment_type' => 'advance',
            'amount' => $validated['amount'],
            'payment_date' => $validated['payment_date'],
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'payment_mode' => $validated['payment_mode'],
            'reference_notes' => $validated['reference_notes'] ?? 'Mid-week advance payout',
            'settled_by' => Auth::id(),
        ]);

        ProjectAuditLog::logChange(
            $worker,
            'advance_issued',
            null,
            null,
            "Advance of ₹{$payment->amount} paid to {$worker->name} via " . strtoupper($payment->payment_mode)
        );

        return back()->with('status', "Advance of ₹{$payment->amount} recorded for {$worker->name}.");
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
        $weekStart = Carbon::parse($validated['week_start'])->toDateString();
        $weekEnd = Carbon::parse($weekStart)->endOfWeek(Carbon::SUNDAY)->toDateString();
        $saturdayEnd = Carbon::parse($weekStart)->addDays(5)->endOfDay()->toDateString();

        $broughtForward = $worker->getBroughtForward($weekStart);
        $earned = $worker->getEarnedInWeek($weekStart, $saturdayEnd);
        $advances = $worker->getAdvancesInWeek($weekStart, $weekEnd);
        $totalDue = max(0, $broughtForward + $earned - $advances);

        $action = $validated['settlement_action'];
        $amountPaid = 0;

        if ($action === 'pay_full') {
            $amountPaid = $totalDue;
        } elseif ($action === 'pay_part') {
            $amountPaid = (float) ($validated['amount_to_pay'] ?? 0);
        } elseif ($action === 'carry_forward') {
            $amountPaid = 0;
        }

        DB::beginTransaction();
        try {
            $payment = null;
            if ($amountPaid > 0) {
                $payment = WagePayment::create([
                    'worker_id' => $worker->id,
                    'payment_type' => 'wage',
                    'amount' => $amountPaid,
                    'payment_date' => now()->toDateString(),
                    'week_start' => $weekStart,
                    'week_end' => $weekEnd,
                    'payment_mode' => $validated['payment_mode'],
                    'reference_notes' => $validated['reference_notes'] ?? "Sunday Payday Settlement ({$action})",
                    'settled_by' => Auth::id(),
                ]);
            }

            // Lock attendance records for this week
            $attendances = ProjectWorkerAttendance::where('worker_id', $worker->id)
                ->whereHas('workDay', function ($q) use ($weekStart, $saturdayEnd) {
                    $q->whereBetween('work_date', [$weekStart, $saturdayEnd]);
                })
                ->get();

            foreach ($attendances as $att) {
                $att->update([
                    'is_paid' => true,
                    'paid_week_start' => $weekStart,
                    'wage_payment_id' => $payment?->id,
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

            return back()->with('status', "Wage settlement for {$worker->name} completed successfully! Paid: ₹{$amountPaid}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to settle wages: ' . $e->getMessage());
        }
    }

    /**
     * Printable Sunday Cash Signature Sheet.
     */
    public function printSignatureSheet(Request $request)
    {
        $weekParam = $request->input('week');
        $weekStart = $weekParam ? Carbon::parse($weekParam)->startOfWeek(Carbon::MONDAY) : Carbon::now()->startOfWeek(Carbon::MONDAY);
        $saturdayEnd = (clone $weekStart)->addDays(5)->endOfDay();
        $paydaySunday = (clone $weekStart)->addDays(6);

        $workers = Worker::where('is_active', true)->orderBy('name')->get();

        $rows = [];
        $totalCash = 0;

        foreach ($workers as $worker) {
            $broughtForward = $worker->getBroughtForward($weekStart->toDateString());
            $earned = $worker->getEarnedInWeek($weekStart->toDateString(), $saturdayEnd->toDateString());
            $advances = $worker->getAdvancesInWeek($weekStart->toDateString(), $paydaySunday->toDateString());
            $totalDue = max(0, $broughtForward + $earned - $advances);

            $attendances = ProjectWorkerAttendance::where('worker_id', $worker->id)
                ->whereHas('workDay', function ($q) use ($weekStart, $saturdayEnd) {
                    $q->whereBetween('work_date', [$weekStart->toDateString(), $saturdayEnd->toDateString()]);
                })
                ->get();

            if ($totalDue > 0 || $attendances->count() > 0) {
                $rows[] = [
                    'worker' => $worker,
                    'days_worked' => $attendances->count(),
                    'metres' => $attendances->sum('cabling_metres'),
                    'brought_forward' => $broughtForward,
                    'earned' => $earned,
                    'advances' => $advances,
                    'total_due' => $totalDue,
                ];
                $totalCash += $totalDue;
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
        $weekStart = $weekParam ? Carbon::parse($weekParam)->startOfWeek(Carbon::MONDAY) : Carbon::now()->startOfWeek(Carbon::MONDAY);
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

        return new StreamedResponse(function () use ($workers, $weekStart, $saturdayEnd, $paydaySunday) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['SecureVision CRM - Sunday Weekly Wage Payout Ledger']);
            fputcsv($handle, ['Week Cycle', $weekStart->format('d M Y') . ' to ' . $saturdayEnd->format('d M Y')]);
            fputcsv($handle, ['Payday Date', $paydaySunday->format('d M Y')]);
            fputcsv($handle, ['Generated At', now()->toDateTimeString()]);
            fputcsv($handle, []);

            fputcsv($handle, [
                'Worker Name',
                'Phone',
                'Daily Rate (₹)',
                'Days Worked',
                'Cabling Metres',
                'Brought Forward (₹)',
                'Earned This Week (₹)',
                'Advances Taken (₹)',
                'Total Payable (₹)',
                'Status'
            ]);

            foreach ($workers as $w) {
                $bf = $w->getBroughtForward($weekStart->toDateString());
                $earned = $w->getEarnedInWeek($weekStart->toDateString(), $saturdayEnd->toDateString());
                $adv = $w->getAdvancesInWeek($weekStart->toDateString(), $paydaySunday->toDateString());
                $due = max(0, $bf + $earned - $adv);

                $attCount = ProjectWorkerAttendance::where('worker_id', $w->id)
                    ->whereHas('workDay', fn($q) => $q->whereBetween('work_date', [$weekStart->toDateString(), $saturdayEnd->toDateString()]))
                    ->count();

                $metres = ProjectWorkerAttendance::where('worker_id', $w->id)
                    ->whereHas('workDay', fn($q) => $q->whereBetween('work_date', [$weekStart->toDateString(), $saturdayEnd->toDateString()]))
                    ->sum('cabling_metres');

                fputcsv($handle, [
                    $w->name,
                    $w->phone ?? '—',
                    number_format($w->daily_rate, 2),
                    $attCount,
                    $metres,
                    number_format($bf, 2),
                    number_format($earned, 2),
                    number_format($adv, 2),
                    number_format($due, 2),
                    $due > 0 ? 'Due for Settlement' : 'Settled'
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
