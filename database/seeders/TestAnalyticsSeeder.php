<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ViewAnalytic;
use App\Models\Series;
use App\Models\Episode;
use App\Models\User;

class TestAnalyticsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::take(5)->get();
        $series = Series::with('seasons.episodes')->get();

        if ($users->isEmpty() || $series->isEmpty()) {
            echo "Pas assez de données. Créez des utilisateurs et des séries d'abord.\n";
            return;
        }

        // Créer des analytics pour les séries
        foreach ($series as $serie) {
            foreach ($serie->seasons as $season) {
                foreach ($season->episodes as $episode) {
                    // Créer 3-10 vues par épisode
                    $viewsCount = rand(3, 10);

                    for ($i = 0; $i < $viewsCount; $i++) {
                        $user = $users->random();
                        $durationWatched = rand(300, 3600); // 5 min à 1h
                        $episodeDuration = 2400; // Supposons 40 min par épisode

                        ViewAnalytic::create([
                            'user_id' => $user->id,
                            'video_id' => null, // Pas de video standalone
                            'series_id' => $serie->id,
                            'episode_id' => $episode->id,
                            'started_at' => now()->subDays(rand(0, 30)),
                            'ended_at' => now()->subDays(rand(0, 30))->addSeconds($durationWatched),
                            'duration_watched' => $durationWatched,
                            'completed' => $durationWatched >= ($episodeDuration * 0.9),
                            'device_type' => ['mobile', 'tablet', 'desktop'][rand(0, 2)],
                            'ip_address' => '192.168.1.' . rand(1, 255),
                        ]);
                    }
                }
            }
        }

        echo "Analytics de test créées avec succès!\n";
        echo "Total analytics: " . ViewAnalytic::count() . "\n";
    }
}
