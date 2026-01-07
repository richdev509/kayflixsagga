<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\WatchLog;
use App\Services\BunnyStreamService;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    protected $bunnyService;

    public function __construct(BunnyStreamService $bunnyService)
    {
        $this->bunnyService = $bunnyService;
    }

    public function index(Request $request)
    {
        $query = Video::with('creator.user')->published();

        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        $videos = $query->latest()->paginate(20);

        // Add full thumbnail URLs
        $videos->getCollection()->transform(function ($video) {
            if ($video->thumbnail_url) {
                // Extract filename from path like /storage/thumbnails/xxx.jpg
                $filename = basename($video->thumbnail_url);
                $video->thumbnail_url = url("/api/thumbnails/{$filename}");
            } elseif ($video->bunny_video_id) {
                $video->thumbnail_url = "https://vz-ea281a7c-17b.b-cdn.net/{$video->bunny_video_id}/thumbnail.jpg";
            }

            // Ajouter l'URL signée pour chaque vidéo
            $video->signed_url = $this->bunnyService->generateSignedUrl($video->bunny_video_id);

            return $video;
        });

        return response()->json($videos);
    }

    public function show($id)
    {
        $video = Video::with('creator.user')->findOrFail($id);

        // Add thumbnail URL if not set
        if (!$video->thumbnail_url) {
            $video->thumbnail_url = "https://vz-ea281a7c-17b.b-cdn.net/{$video->bunny_video_id}/thumbnail.jpg";
        }

        // Ajouter l'URL signée
        $video->signed_url = $this->bunnyService->generateSignedUrl($video->bunny_video_id);

        return response()->json($video);
    }

    public function play(Request $request, $id)
    {
        $user = $request->user();
        $video = Video::findOrFail($id);

        // Check subscription
        $hasActiveSubscription = $user->activeSubscription()->exists();

        if (!$hasActiveSubscription && !$user->hasRole('admin')) {
            return response()->json([
                'message' => 'Abonnement requis pour regarder cette vidéo',
            ], 403);
        }

        // Générer une URL signée pour la vidéo
        $signedUrl = $this->bunnyService->generateSignedUrl($video->bunny_video_id);

        // Get Bunny Stream playback URLs
        $playbackData = [
            'video_id' => $video->bunny_video_id,
            'stream_url' => $signedUrl,
            'thumbnail' => $video->thumbnail_url ?? "https://vz-ea281a7c-17b.b-cdn.net/{$video->bunny_video_id}/thumbnail.jpg",
        ];

        // Increment views
        $video->increment('views_count');

        return response()->json($playbackData);
    }

    public function logWatch(Request $request, $id)
    {
        $request->validate([
            'seconds_watched' => 'required|integer|min:1',
        ]);

        $watchLog = WatchLog::create([
            'user_id' => $request->user()->id,
            'video_id' => $id,
            'seconds_watched' => $request->seconds_watched,
        ]);

        return response()->json([
            'message' => 'Visionnage enregistré',
            'watch_log' => $watchLog,
        ]);
    }

    public function incrementViews(Request $request, $id)
    {
        $video = Video::findOrFail($id);
        $video->increment('views_count');

        return response()->json([
            'message' => 'Vue enregistrée',
            'views_count' => $video->views_count,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create-video');

        $request->validate([
            'vimeo_video_id' => 'required|string|unique:videos',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'duration' => 'nullable|integer',
        ]);

        $creator = $request->user()->creator;

        if (!$creator) {
            return response()->json([
                'message' => 'Vous devez être un créateur approuvé',
            ], 403);
        }

        $video = Video::create([
            'creator_id' => $creator->id,
            'vimeo_video_id' => $request->vimeo_video_id,
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'duration' => $request->duration,
            'status' => 'pending',
        ]);

        return response()->json($video, 201);
    }
}

