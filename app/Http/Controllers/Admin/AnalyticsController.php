<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ViewAnalytic;
use App\Models\Video;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Afficher le tableau de bord des analytics
     */
    public function index(Request $request)
    {
        $period = $request->input('period', '30'); // 7, 30, 90 jours
        $type = $request->input('type', 'all'); // all, videos, series

        $startDate = now()->subDays($period);

        // Top Videos (films standalone)
        $topVideos = ViewAnalytic::select(
                'video_id',
                DB::raw('COUNT(*) as total_views'),
                DB::raw('SUM(duration_watched)/60 as total_minutes'),
                DB::raw('COUNT(DISTINCT user_id) as unique_viewers'),
                DB::raw('SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) as completed_views')
            )
            ->whereNotNull('video_id')
            ->whereNull('series_id') // Only standalone videos, not episodes
            ->where('created_at', '>=', $startDate)
            ->groupBy('video_id')
            ->orderByDesc('total_views')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $video = Video::find($item->video_id);
                return [
                    'id' => $item->video_id,
                    'title' => $video ? $video->title : 'Vidéo #' . $item->video_id,
                    'total_views' => $item->total_views,
                    'total_minutes' => round($item->total_minutes, 2),
                    'unique_viewers' => $item->unique_viewers,
                    'completed_views' => $item->completed_views,
                    'completion_rate' => $item->total_views > 0 ? round(($item->completed_views / $item->total_views) * 100, 2) : 0,
                ];
            });

        // Top Series
        $topSeries = ViewAnalytic::select(
                'series_id',
                DB::raw('COUNT(*) as total_views'),
                DB::raw('SUM(duration_watched)/60 as total_minutes'),
                DB::raw('COUNT(DISTINCT user_id) as unique_viewers'),
                DB::raw('SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) as completed_views')
            )
            ->whereNotNull('series_id')
            ->where('created_at', '>=', $startDate)
            ->groupBy('series_id')
            ->orderByDesc('total_views')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                $series = Series::find($item->series_id);
                return [
                    'id' => $item->series_id,
                    'title' => $series ? $series->title : 'Série #' . $item->series_id,
                    'total_views' => $item->total_views,
                    'total_minutes' => round($item->total_minutes, 2),
                    'unique_viewers' => $item->unique_viewers,
                    'completed_views' => $item->completed_views,
                    'completion_rate' => $item->total_views > 0 ? round(($item->completed_views / $item->total_views) * 100, 2) : 0,
                ];
            });

        // Statistiques globales
        $globalStats = ViewAnalytic::where('created_at', '>=', $startDate)
            ->selectRaw('
                COUNT(*) as total_views,
                SUM(duration_watched)/60 as total_minutes,
                COUNT(DISTINCT user_id) as unique_viewers,
                SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) as completed_views
            ')
            ->first();

        return view('admin.analytics.index', compact('topVideos', 'topSeries', 'globalStats', 'period', 'type'));
    }

    /**
     * Détails d'une vidéo spécifique
     */
    public function videoDetails($videoId)
    {
        $video = Video::findOrFail($videoId);

        // Statistiques globales de la vidéo
        $stats = ViewAnalytic::where('video_id', $videoId)
            ->selectRaw('
                COUNT(*) as total_views,
                SUM(duration_watched)/60 as total_minutes,
                COUNT(DISTINCT user_id) as unique_viewers,
                SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) as completed_views
            ')
            ->first();

        // Vues par jour (30 derniers jours)
        $viewsByDay = ViewAnalytic::where('video_id', $videoId)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as views, SUM(duration_watched)/60 as minutes')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Vues par appareil
        $viewsByDevice = ViewAnalytic::where('video_id', $videoId)
            ->selectRaw('device_type, COUNT(*) as views')
            ->groupBy('device_type')
            ->get();

        return view('admin.analytics.video-details', compact('video', 'stats', 'viewsByDay', 'viewsByDevice'));
    }

    /**
     * Détails d'une série spécifique
     */
    public function seriesDetails($seriesId)
    {
        $series = Series::findOrFail($seriesId);

        // Statistiques globales de la série
        $stats = ViewAnalytic::where('series_id', $seriesId)
            ->selectRaw('
                COUNT(*) as total_views,
                SUM(duration_watched)/60 as total_minutes,
                COUNT(DISTINCT user_id) as unique_viewers,
                SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) as completed_views
            ')
            ->first();

        // Stats par épisode
        $episodeStats = ViewAnalytic::where('series_id', $seriesId)
            ->selectRaw('
                episode_id,
                COUNT(*) as views,
                SUM(duration_watched)/60 as minutes,
                COUNT(DISTINCT user_id) as unique_viewers
            ')
            ->groupBy('episode_id')
            ->get();

        // Vues par jour (30 derniers jours)
        $viewsByDay = ViewAnalytic::where('series_id', $seriesId)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as views, SUM(duration_watched)/60 as minutes')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.analytics.series-details', compact('series', 'stats', 'episodeStats', 'viewsByDay'));
    }
}
