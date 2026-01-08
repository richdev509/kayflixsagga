<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Dispatcher le Job de traitement analytics toutes les 2 minutes
// Traite par lots de 100 pour gérer la haute concurrence
Schedule::job(new \App\Jobs\ProcessViewAnalyticsBatch())->everyTwoMinutes();

// Nettoyer les inscriptions pending expirées (> 24h) tous les jours à 3h du matin
Schedule::command('cleanup:pending-registrations --hours=24')->dailyAt('03:00');
