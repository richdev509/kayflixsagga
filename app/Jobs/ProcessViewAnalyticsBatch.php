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
        $redis = Cache::getRedis();

        // Diviser en chunks pour éviter les requêtes trop grosses
        $chunks = array_chunk($keys, $batchSize);

        foreach ($chunks as $chunk) {
            DB::beginTransaction();

            try {
                foreach ($chunk as $fullKey) {
                    Log::info("Traitement de la clé complète: {$fullKey}");

                    // Utiliser Redis directement avec la clé complète (déjà avec préfixe)
                    $rawData = $redis->get($fullKey);

                    if (!$rawData) {
                        Log::warning("Données manquantes pour la clé complète: {$fullKey}");
                        continue;
                    }

                    // Décoder les données JSON
                    $data = json_decode($rawData, true);

                    if (!$data || !isset($data['session_id'])) {
                        Log::warning("Données invalides pour la clé: {$fullKey}, données: " . $rawData);
                        continue;
                    }

                    Log::info("Données récupérées pour session {$data['session_id']}: durée = {$data['duration_watched']}s");

                    $updateData = [
                        'duration_watched' => $data['duration_watched'],
                        'completed' => $data['completed'] ?? false,
                        'updated_at' => now(),
                    ];

                    // Ajouter ended_at pour les sessions finalisées
                    if ($isFinal && isset($data['ended_at'])) {
                        $updateData['ended_at'] = date('Y-m-d H:i:s', $data['ended_at']);
                    }

                    $updated = ViewAnalytic::where('id', $data['session_id'])
                        ->where('user_id', $data['user_id'])
                        ->update($updateData);

                    if ($updated) {
                        // Supprimer la clé Redis après traitement (utiliser Redis directement)
                        $redis->del($fullKey);
                        $processed++;
                        Log::info("Session {$data['session_id']} traitée et mise à jour avec succès");
                    } else {
                        Log::warning("Aucune session trouvée pour ID {$data['session_id']} et user {$data['user_id']}");
                    }
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

            // Récupérer le préfixe Redis configuré
            $prefix = config('database.redis.options.prefix', '');

            // Chercher avec le préfixe complet
            $searchPattern = '*' . $pattern . '*';
            $allKeys = $redis->keys($searchPattern);

            Log::info("Recherche Redis avec pattern: {$searchPattern}, préfixe: {$prefix}");
            Log::info("Nombre de clés brutes trouvées: " . count($allKeys));

            // Filtrer pour garder uniquement celles qui correspondent au pattern exact
            foreach ($allKeys as $key) {
                // Vérifier que c'est bien view_progress:X ou view_final:X
                if (preg_match('/' . preg_quote($pattern, '/') . ':\d+/', $key)) {
                    $keys[] = $key;
                    Log::info("Clé Redis valide trouvée: {$key}");
                }
            }

            Log::info("Clés Redis trouvées pour pattern {$pattern}: " . count($keys));

            return $keys;

        } catch (\Exception $e) {
            Log::error('Erreur récupération clés Redis: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return [];
        }
    }
}
