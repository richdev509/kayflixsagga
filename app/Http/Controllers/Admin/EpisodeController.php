<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Episode;
use App\Models\Season;
use App\Models\Series;
use App\Services\BunnyStreamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EpisodeController extends Controller
{
    protected $bunnyService;

    public function __construct(BunnyStreamService $bunnyService)
    {
        $this->bunnyService = $bunnyService;
    }

    public function store(Request $request, $seriesId, $seasonId)
    {
        $series = Series::findOrFail($seriesId);

        $season = Season::where('series_id', $seriesId)
            ->findOrFail($seasonId);

        $validator = Validator::make($request->all(), [
            'episode_number' => 'required|integer|min:1',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'bunny_video_id' => 'required|string',
            'duration' => 'required|integer|min:0',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'is_published' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Vérifier si l'épisode existe déjà
        $existingEpisode = Episode::where('season_id', $seasonId)
            ->where('episode_number', $request->episode_number)
            ->first();

        if ($existingEpisode) {
            return response()->json([
                'error' => 'Cet épisode existe déjà pour cette saison',
            ], 422);
        }

        $thumbnailUrl = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
            $thumbnailUrl = basename($thumbnailPath);
        }

        $episode = Episode::create([
            'season_id' => $seasonId,
            'episode_number' => $request->episode_number,
            'title' => $request->title,
            'description' => $request->description,
            'bunny_video_id' => $request->bunny_video_id,
            'duration' => $request->duration,
            'thumbnail_url' => $thumbnailUrl,
            'is_published' => $request->is_published ?? false,
        ]);

        // Mettre à jour le nombre total d'épisodes
        $season->total_episodes = $season->episodes()->count();
        $season->save();

        return response()->json([
            'message' => 'Épisode créé avec succès',
            'episode' => $episode,
        ], 201);
    }

    public function update(Request $request, $seriesId, $seasonId, $id)
    {
        $series = Series::findOrFail($seriesId);

        $season = Season::where('series_id', $seriesId)
            ->findOrFail($seasonId);

        $episode = Episode::where('season_id', $seasonId)
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'episode_number' => 'sometimes|required|integer|min:1',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'nullable|integer|min:0',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'is_published' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Vérifier l'unicité du numéro d'épisode
        if ($request->has('episode_number')) {
            $existingEpisode = Episode::where('season_id', $seasonId)
                ->where('episode_number', $request->episode_number)
                ->where('id', '!=', $id)
                ->first();

            if ($existingEpisode) {
                return response()->json([
                    'error' => 'Ce numéro d\'épisode existe déjà pour cette saison',
                ], 422);
            }
        }

        if ($request->hasFile('thumbnail')) {
            if ($episode->thumbnail_url) {
                Storage::delete('public/thumbnails/' . $episode->thumbnail_url);
            }
            $thumbnailPath = $request->file('thumbnail')->store('public/thumbnails');
            $episode->thumbnail_url = basename($thumbnailPath);
        }

        $episode->fill($request->except(['thumbnail', 'bunny_video_id']));
        $episode->save();

        return response()->json([
            'message' => 'Épisode mis à jour avec succès',
            'episode' => $episode,
        ]);
    }

    public function destroy($seriesId, $seasonId, $id)
    {
        $series = Series::findOrFail($seriesId);

        $season = Season::where('series_id', $seriesId)
            ->findOrFail($seasonId);

        $episode = Episode::where('season_id', $seasonId)
            ->findOrFail($id);

        if ($episode->thumbnail_url) {
            Storage::delete('public/thumbnails/' . $episode->thumbnail_url);
        }

        // Optionnel: Supprimer la vidéo de Bunny.net
        try {
            $this->bunnyService->deleteVideo($episode->bunny_video_id);
        } catch (\Exception $e) {
            // Log l'erreur mais ne bloque pas la suppression
        }

        $episode->delete();

        // Mettre à jour le nombre total d'épisodes
        $season->total_episodes = $season->episodes()->count();
        $season->save();

        return response()->json([
            'message' => 'Épisode supprimé avec succès',
        ]);
    }

    public function publish($seriesId, $seasonId, $id)
    {
        $series = Series::findOrFail($seriesId);

        $season = Season::where('series_id', $seriesId)
            ->findOrFail($seasonId);

        $episode = Episode::where('season_id', $seasonId)
            ->findOrFail($id);

        $episode->is_published = !$episode->is_published;
        $episode->save();

        return response()->json([
            'message' => $episode->is_published ? 'Épisode publié' : 'Épisode dépublié',
            'episode' => $episode,
        ]);
    }

    public function createVideo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $video = $this->bunnyService->createVideo($request->title);
            
            return response()->json([
                'message' => 'Vidéo créée sur Bunny.net',
                'video' => $video,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la création de la vidéo',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function uploadVideo(Request $request, $videoId)
    {
        $validator = Validator::make($request->all(), [
            'video' => 'required|file|mimes:mp4,mov,avi,wmv|max:5242880', // 5GB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $videoFile = $request->file('video');
            $result = $this->bunnyService->uploadVideo($videoId, $videoFile->getRealPath());
            
            return response()->json([
                'message' => 'Vidéo uploadée avec succès',
                'result' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de l\'upload de la vidéo',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getVideoStatus($videoId)
    {
        try {
            $status = $this->bunnyService->getVideoStatus($videoId);
            
            return response()->json([
                'video' => $status,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la récupération du statut',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

