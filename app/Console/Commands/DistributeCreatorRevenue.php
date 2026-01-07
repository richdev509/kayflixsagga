<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Creator;
use App\Models\CreatorPayout;
use App\Models\ViewAnalytic;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class DistributeCreatorRevenue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'revenue:distribute {--month=} {--year=} {--percentage=70}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Distribuer les revenus mensuels aux créateurs basé sur les vues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = $this->option('month') ?? now()->subMonth()->month;
        $year = $this->option('year') ?? now()->subMonth()->year;
        $creatorPercentage = floatval($this->option('percentage')) / 100;

        $this->info("Distribution des revenus pour {$month}/{$year}");
        $this->info("Pourcentage creators: " . ($creatorPercentage * 100) . "%");

        // 1. Calculer les revenus totaux du mois
        $totalRevenue = Subscription::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('status', 'active')
            ->sum('amount');

        if ($totalRevenue <= 0) {
            $this->error("Aucun revenu pour ce mois.");
            return 1;
        }

        $this->info("Revenus totaux du mois: {$totalRevenue}€");

        // 2. Budget à distribuer aux créateurs
        $budgetToDistribute = $totalRevenue * $creatorPercentage;
        $this->info("Budget à distribuer: {$budgetToDistribute}€");

        // 3. Calculer le temps total visionné sur la plateforme
        $totalMinutesWatched = ViewAnalytic::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('duration_watched') / 60;

        if ($totalMinutesWatched <= 0) {
            $this->error("Aucune vue enregistrée pour ce mois.");
            return 1;
        }

        $this->info("Total minutes visionnées: {$totalMinutesWatched}");

        // 4. Distribuer aux créateurs
        $creators = Creator::where('status', 'approved')->get();
        $this->info("Nombre de créateurs actifs: {$creators->count()}");

        $progressBar = $this->output->createProgressBar($creators->count());
        $progressBar->start();

        DB::beginTransaction();

        try {
            foreach ($creators as $creator) {
                // Calculer les minutes visionnées pour ce créateur
                $creatorMinutes = $this->getCreatorMinutesWatched($creator->id, $month, $year);

                if ($creatorMinutes > 0) {
                    // Calculer le revenu du créateur
                    $revenueShare = ($creatorMinutes / $totalMinutesWatched) * 100;
                    $creatorRevenue = ($creatorMinutes / $totalMinutesWatched) * $budgetToDistribute;

                    // Créer le payout
                    CreatorPayout::updateOrCreate(
                        [
                            'creator_id' => $creator->id,
                            'month' => $month,
                            'year' => $year,
                        ],
                        [
                            'minutes_watched' => round($creatorMinutes, 2),
                            'total_platform_minutes' => round($totalMinutesWatched, 2),
                            'revenue_share_percentage' => round($revenueShare, 2),
                            'amount' => round($creatorRevenue, 2),
                            'status' => 'pending',
                        ]
                    );

                    $this->newLine();
                    $this->info("✓ {$creator->user->name}: {$creatorMinutes}min → {$creatorRevenue}€");
                }

                $progressBar->advance();
            }

            DB::commit();
            $progressBar->finish();

            $this->newLine();
            $this->info("✓ Distribution terminée avec succès!");

            // Résumé
            $totalDistributed = CreatorPayout::where('month', $month)
                ->where('year', $year)
                ->sum('amount');

            $this->table(
                ['Métrique', 'Valeur'],
                [
                    ['Revenus totaux', "{$totalRevenue}€"],
                    ['Budget creators (70%)', "{$budgetToDistribute}€"],
                    ['Total distribué', "{$totalDistributed}€"],
                    ['Plateforme (30%)', ($totalRevenue - $totalDistributed) . "€"],
                ]
            );

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Erreur lors de la distribution: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Calculer les minutes visionnées pour un créateur
     */
    private function getCreatorMinutesWatched(int $creatorId, int $month, int $year): float
    {
        // Minutes des vidéos (films)
        $videoMinutes = ViewAnalytic::whereHas('video', function($query) use ($creatorId) {
                $query->where('creator_id', $creatorId);
            })
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('duration_watched') / 60;

        // Minutes des séries
        $seriesMinutes = ViewAnalytic::whereHas('series', function($query) use ($creatorId) {
                $query->where('creator_id', $creatorId);
            })
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('duration_watched') / 60;

        return $videoMinutes + $seriesMinutes;
    }
}

