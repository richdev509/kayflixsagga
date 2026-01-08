<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\ViewAnalytic;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessViewAnalyticsBatch implements ShouldQueue
{
    use Queueable;

    /**
     * Nombre maximum de tentatives
     */
    public $tries = 3;

    /**
     * Délai avant retry en cas d'échec (secondes)
     */
    public $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Démarrage du traitement batch des analytics');

        // Récupérer les clés Redis pour progression et finalisation
        // Utiliser *pattern* pour inclure le préfixe Laravel
        $progressKeys = $this->getRedisKeys('view_progress');
        $finalKeys = $this->getRedisKeys('view_final');

        $totalProcessed = 0;

        // Traiter les sessions en cours (mise à jour)
        if (!empty($progressKeys)) {
            $totalProcessed += $this->processBatch($progressKeys, false);
        }

        // Traiter les sessions finalisées (mise à jour finale + ended_at)
        if (!empty($finalKeys)) {
            $totalProcessed += $this->processBatch($finalKeys, true);
        }

        Log::info("Batch terminé: {$totalProcessed} sessions traitées");
    }

    /**
     * Traiter un lot de sessions
     */
    private function processBatch(array $keys, bool $isFinal): int
    {
        $batchSize = 100; // Traiter par lots de 100
        $processed = 0;

        // Diviser en chunks pour éviter les requêtes trop grosses
        $chunks = array_chunk($keys, $batchSize);

        foreach ($chunks as $chunk) {
            DB::beginTransaction();

            try {
                foreach ($chunk as $fullKey) {
                    // Extraire le nom de clé simple depuis la clé complète Laravel
                    // Ex: "laravel-database-laravel-cache-view_final:7" -> "view_final:7"
                    preg_match('/(view_(?:progress|final):\d+)$/', $fullKey, $matches);
                    $key = $matches[1] ?? null;

                    if (!$key) {
                        Log::warning("Impossible d'extraire la clé de: {$fullKey}");
                        continue;
                    }

                    $data = cache()->get($key);

                    if (!$data || !isset($data['session_id'])) {
                        Log::warning("Données manquantes pour la clé: {$key}");
                        continue;
                    }

                    $updateData = [
                        'duration_watched' => $data['duration_watched'],
                        'completed' => $data['completed'] ?? false,
                        'updated_at' => now(),
                    ];

                    // Ajouter ended_at pour les sessions finalisées
                    if ($isFinal && isset($data['ended_at'])) {
                        $updateData['ended_at'] = date('Y-m-d H:i:s', $data['ended_at']);
                    }

                    ViewAnalytic::where('id', $data['session_id'])
                        ->where('user_id', $data['user_id'])
                        ->update($updateData);

                    // Supprimer la clé Redis après traitement
                    cache()->forget($key);
                    $processed++;

                    Log::info("Session {$data['session_id']} traitée avec succès");
                }

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erreur lors du traitement batch: ' . $e->getMessage());
                // Ne pas bloquer tout le batch, continuer avec le suivant
            }

            // Pause de 100ms entre chaque batch pour éviter de surcharger
            usleep(100000);
        }

        return $processed;
    }

    /**
     * Récupérer les clés Redis par pattern
     */
    private function getRedisKeys(string $pattern): array
    {
        try {
            $redis = Cache::getRedis();
            $keys = [];

            // Predis supporte keys() via __call(), pas besoin de method_exists
            $allKeys = $redis->keys('*' . $pattern . '*');

            // Filtrer pour garder uniquement celles qui correspondent au pattern exact
            foreach ($allKeys as $key) {
                // Vérifier que c'est bien view_progress:X ou view_final:X
                if (preg_match('/' . preg_quote($pattern, '/') . ':\d+/', $key)) {
                    $keys[] = $key;
                }
            }

            Log::info("Clés Redis trouvées pour pattern {$pattern}: " . count($keys));

            return $keys;

        } catch (\Exception $e) {
            Log::error('Erreur récupération clés Redis: ' . $e->getMessage());
            return [];
        }
    }
}
