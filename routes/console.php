<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Alert reminders schedule
\Illuminate\Support\Facades\Schedule::command('alerts:send-reminders')->dailyAt('09:00');


