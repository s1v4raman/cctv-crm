<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

\Illuminate\Support\Facades\Schedule::command('alerts:send-reminders')->dailyAt('09:00');

// Auto-mark employees with no attendance record as Absent every day at 6:00 PM
\Illuminate\Support\Facades\Schedule::command('attendance:mark-absent')->dailyAt('18:00');

