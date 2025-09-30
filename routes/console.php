<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('news:fetch')->daily()
    ->withoutOverlapping(30)
    ->appendOutputTo(storage_path('logs/news-aggregation.log'));

Schedule::command('queue:retry all')->everyFifteenMinutes();
