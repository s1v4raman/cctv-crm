<?php

namespace App\Console\Commands;

use App\Models\EmployeeAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('attendance:mark-absent {--date= : Target date (Y-m-d). Defaults to today.} {--dry-run : Preview changes without saving}')]
#[Description('Mark employees with no attendance record for the day as Absent. Runs automatically at 6 PM daily.')]
class MarkAbsentEmployees extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dateInput = $this->option('date');
        $dryRun    = (bool) $this->option('dry-run');

        $targetDate = $dateInput
            ? Carbon::parse($dateInput)->toDateString()
            : now()->toDateString();

        $this->info("🗓  Target date   : {$targetDate}");
        $this->info("🔍 Dry-run mode  : " . ($dryRun ? 'YES (no changes saved)' : 'NO'));
        $this->newLine();

        // Fetch all internal employees (Admin, Staff, Technician)
        $employees = User::whereIn('role', ['admin', 'staff', 'technician'])->get();

        if ($employees->isEmpty()) {
            $this->warn('No internal employees found.');
            return self::SUCCESS;
        }

        // Fetch employee IDs that already have an attendance record for the target date
        $markedUserIds = EmployeeAttendance::whereDate('date', $targetDate)
            ->pluck('user_id')
            ->toArray();

        // Filter employees with NO record for the day
        $unmarkedEmployees = $employees->whereNotIn('id', $markedUserIds);

        if ($unmarkedEmployees->isEmpty()) {
            $this->info('✅ All employees already have an attendance record for this date. Nothing to do.');
            return self::SUCCESS;
        }

        $this->info("👥 Employees without attendance record: {$unmarkedEmployees->count()}");
        $this->newLine();

        $markedCount = 0;

        foreach ($unmarkedEmployees as $employee) {
            $this->line("  → Marking <fg=yellow>{$employee->name}</> (<fg=gray>{$employee->role}</>) as <fg=red>Absent</>");

            if (!$dryRun) {
                EmployeeAttendance::create([
                    'user_id'        => $employee->id,
                    'date'           => $targetDate,
                    'clock_in'       => null,
                    'clock_out'      => null,
                    'status'         => 'absent',
                    'total_hours'    => 0.00,
                    'overtime_hours' => 0.00,
                    'location_type'  => 'office',
                    'notes'          => 'Auto-marked absent at 6 PM — no check-in recorded.',
                    'marked_by'      => null, // System-generated; no human actor
                ]);
            }

            $markedCount++;
        }

        $this->newLine();

        if ($dryRun) {
            $this->warn("🚫 Dry-run: {$markedCount} employee(s) would have been marked absent.");
        } else {
            $this->info("✅ Marked {$markedCount} employee(s) as Absent for {$targetDate}.");
        }

        return self::SUCCESS;
    }
}
