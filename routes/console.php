<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Free seats from expired booking holds every 15 minutes.
Schedule::command('bookings:release-holds')
    ->everyFifteenMinutes()
    ->withoutOverlapping();
