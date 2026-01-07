<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Series;
use App\Models\Episode;
use App\Services\BunnyStreamService;
use Illuminate\Http\Request;

class SeriesController extends Controller
{
    protected $bunnyService;

    public function __construct(BunnyStreamService $bunnyService)
    {
        $this->bunnyService = $bunnyService;
    }

    public function index()
    {
        $series = Series::with(['creator', 'seasons'])
            ->where('is_published', true)
            ->latest()
            ->paginate(20);

        // Ajouter les URLs complètes pour les miniatures
        $series->getCollection()->transform(function ($serie) {
            if ($serie->thumbnail_url) {
                $serie->thumbnail_url = url("/api/thumbnails/{$serie->thumbnail_url}");
            }
            if ($serie->banner_url) {
                $serie->banner_url = url("/api/banners/{$serie->banner_url}");
            }
            // Générer l'URL signée de la bande-annonce si elle existe
            if ($serie->bunny_trailer_id) {
                $serie->trailer_url = $this->bunnyService->generateSignedUrl($serie->bunny_trailer_id);
            }
            return $serie;
        });

        return response()->json($series);
    }

    public function show($id)
    {
        $series = Series::with(['creator', 'seasons.episodes'])
            ->where('is_published', true)
            ->findOrFail($id);

        // Ajouter les URLs complètes
        if ($series->thumbnail_url) {
            $series->thumbnail_url = url("/api/thumbnails/{$series->thumbnail_url}");
        }
        if ($series->banner_url) {
            $series->banner_url = url("/api/banners/{$series->banner_url}");
        }
        // Générer l'URL signée de la bande-annonce si elle existe
        if ($series->bunny_trailer_id) {
            $series->trailer_url = $this->bunnyService->generateSignedUrl($series->bunny_trailer_id);
        }

        // Transformer les épisodes pour ajouter les URLs signées
        $series->seasons->each(function ($season) {
            $season->episodes->each(function ($episode) {
                if ($episode->thumbnail_url) {
                    $episode->thumbnail_url = url("/api/thumbnails/{$episode->thumbnail_url}");
                }
                $episode->signed_url = $this->bunnyService->generateSignedUrl($episode->bunny_video_id);
            });
            if ($season->thumbnail_url) {
                $season->thumbnail_url = url("/api/thumbnails/{$season->thumbnail_url}");
            }
        });

        // Incrémenter les vues
        $series->increment('views_count');

        return response()->json($series);
    }

    public function getSeasonEpisodes($seriesId, $seasonId)
    {
        $series = Series::where('is_published', true)->findOrFail($seriesId);
        $season = $series->seasons()->with('episodes')->findOrFail($seasonId);

        // Transformer les épisodes
        $season->episodes->transform(function ($episode) {
            if ($episode->thumbnail_url) {
                $episode->thumbnail_url = url("/api/thumbnails/{$episode->thumbnail_url}");
            }
            if ($episode->is_published) {
                $episode->signed_url = $this->bunnyService->generateSignedUrl($episode->bunny_video_id);
            }
            return $episode;
        });

        if ($season->thumbnail_url) {
            $season->thumbnail_url = url("/api/thumbnails/{$season->thumbnail_url}");
        }

        return response()->json($season);
    }

    public function getEpisode($seriesId, $seasonId, $episodeId)
    {
        $series = Series::where('is_published', true)->findOrFail($seriesId);
        $season = $series->seasons()->findOrFail($seasonId);
        $episode = $season->episodes()->where('is_published', true)->findOrFail($episodeId);

        if ($episode->thumbnail_url) {
            $episode->thumbnail_url = url("/api/thumbnails/{$episode->thumbnail_url}");
        }
        $episode->signed_url = $this->bunnyService->generateSignedUrl($episode->bunny_video_id);

        // Incrémenter les vues de l'épisode
        $episode->increment('views_count');

        return response()->json($episode);
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        $series = Series::with(['creator', 'seasons'])
            ->where('is_published', true)
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('category', 'like', "%{$query}%");
            })
            ->latest()
            ->paginate(20);

        $series->getCollection()->transform(function ($serie) {
            if ($serie->thumbnail_url) {
                $serie->thumbnail_url = url("/api/thumbnails/{$serie->thumbnail_url}");
            }
            if ($serie->banner_url) {
                $serie->banner_url = url("/api/banners/{$serie->banner_url}");
            }
            return $serie;
        });

        return response()->json($series);
    }

    public function byCategory($category)
    {
        $series = Series::with(['creator', 'seasons'])
            ->where('is_published', true)
            ->where('category', $category)
            ->latest()
            ->paginate(20);

        $series->getCollection()->transform(function ($serie) {
            if ($serie->thumbnail_url) {
                $serie->thumbnail_url = config('app.url') . "/api/thumbnails/{$serie->thumbnail_url}";
            }
            if ($serie->banner_url) {
                $serie->banner_url = config('app.url') . "/api/banners/{$serie->banner_url}";
            }
            return $serie;
        });

        return response()->json($series);
    }
}

