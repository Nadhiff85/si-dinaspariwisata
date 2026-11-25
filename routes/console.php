<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily scheduled ETL & Analytics Model refresh at 02:00 AM (PRD Section 9.3)
Schedule::command('etl:run')->dailyAt('02:00');
