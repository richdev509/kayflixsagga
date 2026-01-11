<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\Creator;
use App\Services\BunnyStreamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    protected $bunnyService;

    public function __construct(BunnyStreamService $bunnyService)
    {
        $this->bunnyService = $bunnyService;
    }

    public function index()
    {
        $videos = Video::with('creator.user')
            ->latest()
            ->paginate(20);

        return view('admin.videos.index', compact('videos'));
    }

    public function create()
    {
        $creators = Creator::with('user')
            ->where('status', 'approved')
            ->get();

        return view('admin.videos.create', compact('creators'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'creator_id' => 'required|exists:creators,id',
            'category' => 'required|string',
            'video_file' => 'required|file|mimes:mp4,mov,avi,wmv|max:2048000', // 2GB max
            'thumbnail' => 'nullable|image|max:5120', // 5MB max
        ]);

        try {
            // 1. Créer la vidéo dans Bunny.net
            $bunnyVideo = $this->bunnyService->createVideo($request->title);

            if (!$bunnyVideo || !isset($bunnyVideo['guid'])) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur lors de la création de la vidéo sur Bunny.net'
                    ], 500);
                }
                return back()->withErrors(['error' => 'Erreur lors de la création de la vidéo sur Bunny.net']);
            }

            // 2. Upload du fichier vidéo vers Bunny
            $videoFile = $request->file('video_file');
            $videoPath = $videoFile->getRealPath();

            $uploadResult = $this->bunnyService->uploadVideo(
                $bunnyVideo['guid'],
                $videoPath
            );

            if (!$uploadResult) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur lors de l\'upload de la vidéo vers Bunny.net'
                    ], 500);
                }
                return back()->withErrors(['error' => 'Erreur lors de l\'upload de la vidéo']);
            }

            // 3. Upload de la thumbnail si fournie
            $thumbnailUrl = null;
            if ($request->hasFile('thumbnail')) {
                try {
                    $thumbnailFile = $request->file('thumbnail');
                    Log::info('Uploading thumbnail', [
                        'original_name' => $thumbnailFile->getClientOriginalName(),
                        'size' => $thumbnailFile->getSize(),
                        'mime' => $thumbnailFile->getMimeType()
                    ]);

                    $thumbnailPath = $thumbnailFile->store('thumbnails', 'public');

                    if ($thumbnailPath) {
                        $thumbnailUrl = Storage::url($thumbnailPath);
                        Log::info('Thumbnail uploaded successfully', [
                            'path' => $thumbnailPath,
                            'url' => $thumbnailUrl
                        ]);
                    } else {
                        Log::error('Failed to store thumbnail file');
                    }
                } catch (\Exception $e) {
                    Log::error('Thumbnail upload error', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            } else {
                Log::warning('No thumbnail file in request');
            }

            // 4. Créer l'entrée dans la base de données
            $video = Video::create([
                'creator_id' => $request->creator_id,
                'title' => $request->title,
                'description' => $request->description,
                'bunny_video_id' => $bunnyVideo['guid'],
                'thumbnail_url' => $thumbnailUrl,
                'duration' => 0, // Sera mis à jour par webhook Bunny
                'category' => $request->category,
                'is_published' => false, // En attente d'encodage
            ]);

            // Return JSON for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Vidéo uploadée avec succès! L\'encodage est en cours sur Bunny.net...',
                    'video_id' => $video->id,
                    'bunny_video_id' => $bunnyVideo['guid'],
                    'video' => $video
                ], 201);
            }

            return redirect()
                ->route('admin.videos.show', $video)
                ->with('success', 'Vidéo uploadée avec succès. L\'encodage est en cours...');

        } catch (\Exception $e) {
            \Log::error('Video upload error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur: ' . $e->getMessage()
                ], 500);
            }

            return back()->withErrors(['error' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    public function show(Video $video)
    {
        $video->load('creator.user');

        // Récupérer les infos depuis Bunny
        $bunnyVideo = null;
        if ($video->bunny_video_id) {
            $bunnyVideo = $this->bunnyService->getVideo($video->bunny_video_id);
        }

        return view('admin.videos.show', compact('video', 'bunnyVideo'));
    }

    public function edit(Video $video)
    {
        $creators = Creator::with('user')
            ->where('status', 'approved')
            ->get();

        return view('admin.videos.edit', compact('video', 'creators'));
    }

    public function update(Request $request, Video $video)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'is_published' => 'boolean',
            'thumbnail' => 'nullable|image|max:5120',
        ]);

        $data = $request->only(['title', 'description', 'category', 'is_published']);

        // Upload nouvelle thumbnail si fournie
        if ($request->hasFile('thumbnail')) {
            // Supprimer l'ancienne thumbnail
            if ($video->thumbnail_url) {
                $oldPath = str_replace('/storage/', '', $video->thumbnail_url);
                Storage::disk('public')->delete($oldPath);
            }

            $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
            $data['thumbnail_url'] = Storage::url($thumbnailPath);
        }

        // Mettre à jour sur Bunny.net
        if ($video->bunny_video_id) {
            $this->bunnyService->updateVideo($video->bunny_video_id, [
                'title' => $request->title,
            ]);
        }

        $video->update($data);

        return redirect()
            ->route('admin.videos.show', $video)
            ->with('success', 'Vidéo mise à jour avec succès');
    }

    public function destroy(Video $video)
    {
        try {
            // Supprimer de Bunny.net
            if ($video->bunny_video_id) {
                $this->bunnyService->deleteVideo($video->bunny_video_id);
            }

            // Supprimer la thumbnail
            if ($video->thumbnail_url) {
                $path = str_replace('/storage/', '', $video->thumbnail_url);
                Storage::disk('public')->delete($path);
            }

            $video->delete();

            return redirect()
                ->route('admin.videos.index')
                ->with('success', 'Vidéo supprimée avec succès');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    public function togglePublish(Video $video)
    {
        $video->update(['is_published' => !$video->is_published]);

        $status = $video->is_published ? 'publiée' : 'dépubliée';
        return redirect()->back()->with('success', "Vidéo $status avec succès");
    }

    public function refreshFromBunny(Video $video)
    {
        if (!$video->bunny_video_id) {
            return back()->withErrors(['error' => 'Aucun ID Bunny associé']);
        }

        try {
            $bunnyVideo = $this->bunnyService->getVideo($video->bunny_video_id);

            if ($bunnyVideo && isset($bunnyVideo['status'])) {
                $updates = [
                    'duration' => $bunnyVideo['length'] ?? $video->duration,
                ];

                // Si l'encodage est terminé, publier automatiquement
                if ($bunnyVideo['status'] === 4) { // 4 = Encoded
                    $updates['is_published'] = true;

                    // Récupérer la thumbnail depuis Bunny si pas de thumbnail locale
                    if (!$video->thumbnail_url) {
                        $updates['thumbnail_url'] = $this->bunnyService->getThumbnailUrl($video->bunny_video_id);
                    }
                }

                $video->update($updates);

                return redirect()->back()->with('success', 'Informations mises à jour depuis Bunny.net');
            }

            return back()->withErrors(['error' => 'Impossible de récupérer les infos depuis Bunny']);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur: ' . $e->getMessage()]);
        }
    }
}
