<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Series;
use App\Models\Video;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * Récupère les bannières pour l'écran de connexion
     * Mélange des séries et vidéos avec leurs images
     */
    public function index(Request $request)
    {
        try {
            $limit = $request->query('limit', 10);
            $baseUrl = config('app.url');

            // Récupérer les séries publiées avec bannières
            $series = Series::where('is_published', true)
                ->whereNotNull('banner_url')
                ->where('banner_url', '!=', '')
                ->select('id', 'title', 'banner_url', 'thumbnail_url')
                ->inRandomOrder()
                ->limit($limit / 2)
                ->get()
                ->map(function ($serie) use ($baseUrl) {
                    $bannerUrl = $serie->banner_url ?? '';
                    $thumbnailUrl = $serie->thumbnail_url ?? '';

                    // Construire les URLs complètes
                    if ($bannerUrl && !filter_var($bannerUrl, FILTER_VALIDATE_URL)) {
                        $bannerUrl = $baseUrl . '/storage/banners/' . $bannerUrl;
                    }
                    if ($thumbnailUrl && !filter_var($thumbnailUrl, FILTER_VALIDATE_URL)) {
                        $thumbnailUrl = $baseUrl . '/storage/thumbnails/' . $thumbnailUrl;
                    }

                    return [
                        'id' => $serie->id,
                        'type' => 'series',
                        'title' => $serie->title,
                        'banner' => $bannerUrl,
                        'thumbnail' => $thumbnailUrl,
                    ];
                });

            // Récupérer les vidéos publiées avec thumbnails
            $videos = Video::where('is_published', true)
                ->whereNotNull('thumbnail_url')
                ->where('thumbnail_url', '!=', '')
                ->select('id', 'title', 'thumbnail_url')
                ->inRandomOrder()
                ->limit($limit / 2)
                ->get()
                ->map(function ($video) use ($baseUrl) {
                    $thumbnailUrl = $video->thumbnail_url ?? '';

                    // Construire l'URL complète
                    if ($thumbnailUrl && !filter_var($thumbnailUrl, FILTER_VALIDATE_URL)) {
                        $thumbnailUrl = $baseUrl . '/storage/thumbnails/' . $thumbnailUrl;
                    }

                    return [
                        'id' => $video->id,
                        'type' => 'video',
                        'title' => $video->title,
                        'banner' => '',
                        'thumbnail' => $thumbnailUrl,
                    ];
                });

            // Mélanger séries et vidéos
            $banners = $series->concat($videos)->shuffle()->values();

            return response()->json([
                'success' => true,
                'data' => $banners,
                'count' => $banners->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des bannières',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
