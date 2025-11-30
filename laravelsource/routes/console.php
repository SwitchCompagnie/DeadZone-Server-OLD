<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule maintenance broadcast every 30 seconds when maintenance mode is active
Schedule::command('maintenance:broadcast')
    ->everyThirtySeconds()
    ->withoutOverlapping()
    ->runInBackground();
