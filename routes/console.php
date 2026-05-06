<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:send-deadline-reminders')->dailyAt('06:00');
Schedule::command('app:ai-legal-watcher')->mondays()->at('06:30');
