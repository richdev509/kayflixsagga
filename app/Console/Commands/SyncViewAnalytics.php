<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ViewAnalytic;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SyncViewAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:sync {--force : Force sync même si pas de changements}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchroniser les données de cache vers la base de données (toutes les 5 minutes)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Démarrage de la synchronisation des analytics...');

        // Récupérer toutes les clés de cache view_progress:*
        $pattern = 'view_progress:*';
        $keys = $this->getCacheKeys($pattern);

        if (empty($keys)) {
            $this->info('Aucune donnée en cache à synchroniser.');
            return 0;
        }

        $this->info('Trouvé ' . count($keys) . ' sessions en cache');
        $progressBar = $this->output->createProgressBar(count($keys));
        $progressBar->start();

        $synced = 0;
        $failed = 0;

        DB::beginTransaction();

        try {
            foreach ($keys as $key) {
                $data = cache()->get($key);

                if ($data && isset($data['session_id'])) {
                    try {
                        ViewAnalytic::where('id', $data['session_id'])
                            ->where('user_id', $data['user_id'])
                            ->update([
                                'duration_watched' => $data['duration_watched'],
                                'completed' => $data['completed'] ?? false,
                                'updated_at' => now(),
                            ]);

                        $synced++;

                    } catch (\Exception $e) {
                        $failed++;
                        $this->error("\nErreur session {$data['session_id']}: " . $e->getMessage());
                    }
                }

                $progressBar->advance();
            }

            DB::commit();
            $progressBar->finish();

            $this->newLine();
            $this->info("✓ Synchronisation terminée!");
            $this->table(
                ['Métrique', 'Valeur'],
                [
                    ['Sessions synchronisées', $synced],
                    ['Échecs', $failed],
                    ['Total', count($keys)],
                ]
            );

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Erreur lors de la synchronisation: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Récupérer les clés de cache par pattern
     */
    private function getCacheKeys(string $pattern): array
    {
        // Pour Redis
        if (config('cache.default') === 'redis') {
            $redis = Cache::getRedis();
            return $redis->keys($pattern);
        }

        // Pour file cache (moins efficace)
        // Dans ce cas, on retourne un tableau vide
        // Il faudrait utiliser Redis en production
        return [];
    }
}

