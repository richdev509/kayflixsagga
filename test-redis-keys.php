<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

echo "=== Test récupération clés Redis ===\n\n";

$redis = Cache::getRedis();

// Test 1: Toutes les clés
$allKeys = $redis->keys('*');
echo "Toutes les clés (" . count($allKeys) . "):\n";
foreach ($allKeys as $key) {
    echo "  - $key\n";
}

echo "\n";

// Test 2: Clés view_final
$finalKeys = $redis->keys('*view_final*');
echo "Clés view_final (" . count($finalKeys) . "):\n";
foreach ($finalKeys as $key) {
    echo "  - $key\n";
}

echo "\n";

// Test 3: Clés view_progress
$progressKeys = $redis->keys('*view_progress*');
echo "Clés view_progress (" . count($progressKeys) . "):\n";
foreach ($progressKeys as $key) {
    echo "  - $key\n";
}

echo "\n";

// Test 4: Essayer de lire une clé
if (!empty($finalKeys)) {
    $firstKey = $finalKeys[0];
    echo "Test lecture de la première clé: $firstKey\n";
    
    // Extraire le nom simple
    preg_match('/(view_final:\d+)$/', $firstKey, $matches);
    $simpleName = $matches[1] ?? null;
    
    echo "Nom simple extrait: $simpleName\n";
    
    if ($simpleName) {
        $data = cache()->get($simpleName);
        echo "Données:\n";
        print_r($data);
    }
}
