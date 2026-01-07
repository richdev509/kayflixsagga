<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Creator;
use App\Models\CreatorPayout;
use App\Models\ViewAnalytic;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevenueController extends Controller
{
    /**
     * Afficher la page de distribution des revenus
     */
    public function index()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Statistiques du mois en cours
        $currentMonthStats = $this->getMonthStats($currentMonth, $currentYear);

        // Historique des distributions
        $payouts = CreatorPayout::with('creator.user')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(20);

        // Paiements en attente
        $pendingPayouts = CreatorPayout::with('creator.user')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.revenue.index', compact('currentMonthStats', 'payouts', 'pendingPayouts'));
    }

    /**
     * Distribuer les revenus d'un mois
     */
    public function distribute(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2100',
            'percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $month = $request->month;
        $year = $request->year;
        $creatorPercentage = ($request->percentage ?? 70) / 100;

        try {
            DB::beginTransaction();

            // 1. Calculer les revenus totaux du mois
            $totalRevenue = Subscription::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->where('status', 'active')
                ->sum('amount');

            if ($totalRevenue <= 0) {
                return back()->with('error', 'Aucun revenu pour ce mois.');
            }

            // 2. Budget à distribuer
            $budgetToDistribute = $totalRevenue * $creatorPercentage;

            // 3. Temps total visionné
            $totalMinutesWatched = ViewAnalytic::whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->sum('duration_watched') / 60;

            if ($totalMinutesWatched <= 0) {
                return back()->with('error', 'Aucune vue enregistrée pour ce mois.');
            }

            // 4. Distribuer aux créateurs
            $creators = Creator::where('status', 'approved')->get();
            $distributedCount = 0;

            foreach ($creators as $creator) {
                $creatorMinutes = $this->getCreatorMinutesWatched($creator->id, $month, $year);

                if ($creatorMinutes > 0) {
                    $revenueShare = ($creatorMinutes / $totalMinutesWatched) * 100;
                    $creatorRevenue = ($creatorMinutes / $totalMinutesWatched) * $budgetToDistribute;

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

                    $distributedCount++;
                }
            }

            DB::commit();

            return back()->with('success', "Revenus distribués avec succès à {$distributedCount} créateurs pour {$month}/{$year}!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la distribution: ' . $e->getMessage());
        }
    }

    /**
     * Marquer un paiement comme payé
     */
    public function markAsPaid(Request $request, $id)
    {
        $request->validate([
            'stripe_transfer_id' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $payout = CreatorPayout::findOrFail($id);

            $payout->update([
                'status' => 'paid',
                'paid_at' => now(),
                'stripe_transfer_id' => $request->stripe_transfer_id,
                'notes' => $request->notes,
            ]);

            return back()->with('success', 'Paiement marqué comme effectué!');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Obtenir les statistiques d'un mois
     */
    private function getMonthStats($month, $year)
    {
        $totalRevenue = Subscription::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('status', 'active')
            ->sum('amount');

        $totalMinutesWatched = ViewAnalytic::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('duration_watched') / 60;

        $uniqueViewers = ViewAnalytic::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->distinct('user_id')
            ->count('user_id');

        $totalViews = ViewAnalytic::whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->count();

        $distributedAmount = CreatorPayout::where('month', $month)
            ->where('year', $year)
            ->sum('amount');

        return [
            'total_revenue' => $totalRevenue,
            'total_minutes_watched' => round($totalMinutesWatched, 2),
            'unique_viewers' => $uniqueViewers,
            'total_views' => $totalViews,
            'distributed_amount' => $distributedAmount,
            'platform_amount' => $totalRevenue - $distributedAmount,
        ];
    }

    /**
     * Calculer les minutes visionnées pour un créateur
     */
    private function getCreatorMinutesWatched($creatorId, $month, $year)
    {
        $videoMinutes = ViewAnalytic::whereHas('video', function($query) use ($creatorId) {
                $query->where('creator_id', $creatorId);
            })
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('duration_watched') / 60;

        $seriesMinutes = ViewAnalytic::whereHas('series', function($query) use ($creatorId) {
                $query->where('creator_id', $creatorId);
            })
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('duration_watched') / 60;

        return $videoMinutes + $seriesMinutes;
    }

    /**
     * Afficher les détails d'un créateur
     */
    public function creatorDetails($creatorId, Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $creator = Creator::with('user')->findOrFail($creatorId);

        $stats = [
            'minutes_watched' => $this->getCreatorMinutesWatched($creatorId, $month, $year),
            'unique_viewers' => ViewAnalytic::forCreator($creatorId)
                ->forMonth($month, $year)
                ->distinct('user_id')
                ->count('user_id'),
            'total_views' => ViewAnalytic::forCreator($creatorId)
                ->forMonth($month, $year)
                ->count(),
        ];

        $payouts = CreatorPayout::where('creator_id', $creatorId)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return view('admin.revenue.creator-details', compact('creator', 'stats', 'payouts', 'month', 'year'));
    }
}
