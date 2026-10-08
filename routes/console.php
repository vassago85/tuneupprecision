<?php

use App\Models\EmailLog;
use Illuminate\Support\Facades\Schedule;

// Free seats from expired booking holds every 15 minutes.
Schedule::command('bookings:release-holds')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

Schedule::command('model:prune', ['--model' => [EmailLog::class]])
    ->daily();
