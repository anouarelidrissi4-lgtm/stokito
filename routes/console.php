<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Public demo (/demo) is rebuilt every night so visitors always find clean sample data.
\Illuminate\Support\Facades\Schedule::command('demo:reset')->dailyAt('03:40');
\Illuminate\Support\Facades\Schedule::command('factures:relancer')->dailyAt('09:00');
\Illuminate\Support\Facades\Schedule::command('caisse:cloturer')->dailyAt('00:15');
