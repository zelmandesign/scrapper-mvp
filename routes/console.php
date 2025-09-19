<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('profiles:rescrape --tier=high')
    ->dailyAt('02:10')
    ->before(function () {
        Log::info('[scheduler] Starting rescrape: tier=high');
    })
    ->after(function () {
        Log::info('[scheduler] Finished rescrape: tier=high');
    })
    ->onOneServer()
    ->withoutOverlapping();

Schedule::command('profiles:rescrape --tier=normal')
    ->cron('10 3 */3 * *')
    ->before(function () {
        Log::info('[scheduler] Starting rescrape: tier=normal');
    })
    ->after(function () {
        Log::info('[scheduler] Finished rescrape: tier=normal');
    })
    ->onOneServer()
    ->withoutOverlapping();
