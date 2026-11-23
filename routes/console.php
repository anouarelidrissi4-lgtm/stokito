<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Public demo (/demo) is rebuilt every night so visitors always find clean sample data.
\Illuminate\Support\Facades\Schedule::command('demo:reset')->dailyAt('03:40');
