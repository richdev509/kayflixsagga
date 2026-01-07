<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Jobs\ProcessViewAnalyticsBatch;
use Illuminate\Support\Facades\Log;

echo "=== Test manuel du Job ProcessViewAnalyticsBatch ===\n\n";

// Activer les logs
config(['logging.default' => 'single']);

// Créer et exécuter le job
$job = new ProcessViewAnalyticsBatch();
$job->handle();

echo "\nJob exécuté !\n";
echo "Vérifiez storage/logs/laravel.log pour les détails\n";
