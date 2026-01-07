<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Cache;

echo "=== Debug getRedisKeys ===\n\n";

$pattern = 'view_final';
$redis = Cache::getRedis();

echo "Pattern recherché: {$pattern}\n";
echo "Méthode keys existe: " . (method_exists($redis, 'keys') ? 'OUI' : 'NON') . "\n\n";

// Test 1: Ce que fait le script qui marche
$allKeys1 = $redis->keys('*view_final*');
echo "Test 1 - keys('*view_final*'): " . count($allKeys1) . " résultats\n";
foreach ($allKeys1 as $key) {
    echo "  - $key\n";
}

echo "\n";

// Test 2: Ce que fait le Job
$allKeys2 = $redis->keys('*' . $pattern . '*');
echo "Test 2 - keys('*{$pattern}*'): " . count($allKeys2) . " résultats\n";
foreach ($allKeys2 as $key) {
    echo "  - $key\n";
}

echo "\n";

// Test 3: Filtrage regex
echo "Test 3 - Filtrage avec regex:\n";
foreach ($allKeys2 as $key) {
    if (preg_match('/' . preg_quote($pattern, '/') . ':\d+/', $key)) {
        echo "  MATCH: $key\n";
    } else {
        echo "  NO MATCH: $key\n";
    }
}
