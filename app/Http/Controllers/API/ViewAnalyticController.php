<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ViewAnalytic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ViewAnalyticController extends Controller
{
    /**
     * Démarrer une session de visionnage
     */
    public function start(Request $request)
    {
        $validated = $request->validate([
            'video_id' => 'nullable|exists:videos,id',
            'series_id' => 'nullable|exists:series,id',
            'episode_id' => 'nullable|exists:episodes,id',
            'device_type' => 'nullable|string|in:mobile,tablet,desktop',
        ]);

        try {
            $viewAnalytic = ViewAnalytic::create([
                'user_id' => auth()->id(),
                'video_id' => $validated['video_id'] ?? null,
                'series_id' => $validated['series_id'] ?? null,
                'episode_id' => $validated['episode_id'] ?? null,
                'started_at' => now(),
                'device_type' => $validated['device_type'] ?? 'mobile',
                'ip_address' => $request->ip(),
                'duration_watched' => 0,
            ]);

            return response()->json([
                'success' => true,
                'session_id' => $viewAnalytic->id,
                'message' => 'Session de visionnage démarrée',
            ]);

        } catch (\Exception $e) {
            Log::error('Error starting view session: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du démarrage de la session',
            ], 500);
        }
    }

    /**
     * Mettre à jour la progression du visionnage (optimisé pour charge élevée)
     */
    public function update(Request $request, $sessionId)
    {
        $validated = $request->validate([
            'duration_watched' => 'required|integer|min:0',
            'completed' => 'nullable|boolean',
        ]);

        try {
            // Récupérer la session depuis la BDD pour avoir video_id, series_id, episode_id
            $viewAnalytic = ViewAnalytic::find($sessionId);
            
            if (!$viewAnalytic) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session introuvable',
                ], 404);
            }

            // Utiliser Redis Cache au lieu d'écrire directement en BDD
            $cacheKey = "view_progress:{$sessionId}";

            cache()->put($cacheKey, [
                'session_id' => $sessionId,
                'user_id' => auth()->id(),
                'video_id' => $viewAnalytic->video_id,
                'series_id' => $viewAnalytic->series_id,
                'episode_id' => $viewAnalytic->episode_id,
                'duration_watched' => $validated['duration_watched'],
                'completed' => $validated['completed'] ?? false,
                'updated_at' => now()->timestamp,
            ], now()->addMinutes(60)); // Expire après 1h

            // Réponse immédiate sans attendre la BDD
            return response()->json([
                'success' => true,
                'message' => 'Progression mise à jour',
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating view session: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour',
            ], 500);
        }
    }

    /**
     * Terminer une session de visionnage (optimisé pour haute concurrence)
     */
    public function end(Request $request, $sessionId)
    {
        $validated = $request->validate([
            'duration_watched' => 'required|integer|min:0',
            'completed' => 'nullable|boolean',
        ]);

        try {
            // Récupérer depuis cache si existe
            $cacheKey = "view_progress:{$sessionId}";
            $cachedData = cache()->get($cacheKey);

            // Utiliser les données du cache ou de la requête
            $durationWatched = $cachedData['duration_watched'] ?? $validated['duration_watched'];
            $completed = $cachedData['completed'] ?? $validated['completed'] ?? false;

            // Marquer comme "à finaliser" dans Redis au lieu de MySQL direct
            // Évite le goulot d'étranglement avec 1500+ fermetures simultanées
            cache()->put("view_final:{$sessionId}", [
                'session_id' => $sessionId,
                'user_id' => auth()->id(),
                'video_id' => $cachedData['video_id'] ?? null,
                'series_id' => $cachedData['series_id'] ?? null,
                'episode_id' => $cachedData['episode_id'] ?? null,
                'duration_watched' => $durationWatched,
                'completed' => $completed,
                'ended_at' => now()->timestamp,
            ], now()->addHours(2)); // Garde 2h pour être sûr de synchroniser

            // Nettoyer le cache de progression
            cache()->forget($cacheKey);

            // Réponse immédiate sans attendre MySQL
            return response()->json([
                'success' => true,
                'message' => 'Session de visionnage terminée',
            ]);

        } catch (\Exception $e) {
            Log::error('Error ending view session: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la fermeture de la session',
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques d'une vidéo spécifique
     */
    public function videoStats(Request $request, $videoId)
    {
        try {
            $totalMinutesWatched = ViewAnalytic::where('video_id', $videoId)
                ->sum('duration_watched') / 60;

            $totalViews = ViewAnalytic::where('video_id', $videoId)
                ->count();

            $uniqueViewers = ViewAnalytic::where('video_id', $videoId)
                ->distinct('user_id')
                ->count('user_id');

            $completedViews = ViewAnalytic::where('video_id', $videoId)
                ->where('completed', true)
                ->count();

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_minutes_watched' => round($totalMinutesWatched, 2),
                    'total_views' => $totalViews,
                    'unique_viewers' => $uniqueViewers,
                    'completed_views' => $completedViews,
                    'completion_rate' => $totalViews > 0 ? round(($completedViews / $totalViews) * 100, 2) : 0,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting video stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques d'une série spécifique
     */
    public function seriesStats(Request $request, $seriesId)
    {
        try {
            $totalMinutesWatched = ViewAnalytic::where('series_id', $seriesId)
                ->sum('duration_watched') / 60;

            $totalViews = ViewAnalytic::where('series_id', $seriesId)
                ->count();

            $uniqueViewers = ViewAnalytic::where('series_id', $seriesId)
                ->distinct('user_id')
                ->count('user_id');

            $completedViews = ViewAnalytic::where('series_id', $seriesId)
                ->where('completed', true)
                ->count();

            // Stats par épisode
            $episodeStats = ViewAnalytic::where('series_id', $seriesId)
                ->selectRaw('episode_id, COUNT(*) as views, SUM(duration_watched)/60 as minutes')
                ->groupBy('episode_id')
                ->get();

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_minutes_watched' => round($totalMinutesWatched, 2),
                    'total_views' => $totalViews,
                    'unique_viewers' => $uniqueViewers,
                    'completed_views' => $completedViews,
                    'completion_rate' => $totalViews > 0 ? round(($completedViews / $totalViews) * 100, 2) : 0,
                    'episodes_stats' => $episodeStats,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting series stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques d'un épisode spécifique
     */
    public function episodeStats(Request $request, $episodeId)
    {
        try {
            $totalMinutesWatched = ViewAnalytic::where('episode_id', $episodeId)
                ->sum('duration_watched') / 60;

            $totalViews = ViewAnalytic::where('episode_id', $episodeId)
                ->count();

            $uniqueViewers = ViewAnalytic::where('episode_id', $episodeId)
                ->distinct('user_id')
                ->count('user_id');

            $completedViews = ViewAnalytic::where('episode_id', $episodeId)
                ->where('completed', true)
                ->count();

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_minutes_watched' => round($totalMinutesWatched, 2),
                    'total_views' => $totalViews,
                    'unique_viewers' => $uniqueViewers,
                    'completed_views' => $completedViews,
                    'completion_rate' => $totalViews > 0 ? round(($completedViews / $totalViews) * 100, 2) : 0,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting episode stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques d'un creator
     */
    public function creatorStats(Request $request, $creatorId)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        try {
            $totalMinutesWatched = ViewAnalytic::forCreator($creatorId)
                ->forMonth($month, $year)
                ->sum('duration_watched') / 60;

            $totalViews = ViewAnalytic::forCreator($creatorId)
                ->forMonth($month, $year)
                ->count();

            $uniqueViewers = ViewAnalytic::forCreator($creatorId)
                ->forMonth($month, $year)
                ->distinct('user_id')
                ->count('user_id');

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_minutes_watched' => round($totalMinutesWatched, 2),
                    'total_views' => $totalViews,
                    'unique_viewers' => $uniqueViewers,
                    'month' => $month,
                    'year' => $year,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting creator stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
            ], 500);
        }
    }
}


