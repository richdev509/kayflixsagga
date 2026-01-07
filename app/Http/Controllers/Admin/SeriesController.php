<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SeriesController extends Controller
{
    public function index(Request $request)
    {
        // Si requête AJAX, retourner JSON
        if ($request->wantsJson()) {
            $series = Series::with(['creator', 'seasons'])
                ->latest()
                ->paginate(10);

            return response()->json($series);
        }

        // Sinon retourner la vue
        $creators = \App\Models\Creator::where('status', 'approved')->with('user')->get();
        return view('admin.series.index', compact('creators'));
    }

    public function store(Request $request)
    {
        \Log::info('SeriesController::store - Données reçues', $request->all());

        $validator = Validator::make($request->all(), [
            'creator_id' => 'required|exists:creators,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'release_year' => 'required|integer|min:1900|max:' . (date('Y') + 5),
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'banner' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
            'trailer_url' => 'nullable|url|max:500',
            'is_published' => 'boolean',
        ]);

        if ($validator->fails()) {
            \Log::error('SeriesController::store - Validation échouée', $validator->errors()->toArray());
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $thumbnailUrl = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
            $thumbnailUrl = basename($thumbnailPath);
        }

        $bannerUrl = null;
        if ($request->hasFile('banner')) {
            $bannerPath = $request->file('banner')->store('banners', 'public');
            $bannerUrl = basename($bannerPath);
        }

        try {
            $series = Series::create([
                'creator_id' => $request->creator_id,
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'release_year' => $request->release_year,
                'thumbnail_url' => $thumbnailUrl,
                'banner_url' => $bannerUrl,
                'trailer_url' => $request->trailer_url,
                'is_published' => $request->is_published ?? false,
            ]);

            \Log::info('SeriesController::store - Série créée', ['id' => $series->id]);

            return response()->json([
                'message' => 'Série créée avec succès',
                'series' => $series->load('seasons'),
            ], 201);
        } catch (\Exception $e) {
            \Log::error('SeriesController::store - Erreur création', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Erreur lors de la création de la série',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id, Request $request)
    {
        $series = Series::with(['creator', 'seasons.episodes'])
            ->findOrFail($id);

        // Si requête AJAX, retourner JSON
        if ($request->wantsJson()) {
            return response()->json($series);
        }

        // Sinon retourner la vue
        return view('admin.series.show', compact('series'));
    }

    public function update(Request $request, $id)
    {
        $series = Series::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'release_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg|max:10240',
            'trailer_url' => 'nullable|url|max:500',
            'bunny_trailer_id' => 'nullable|string|max:255',
            'is_published' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('thumbnail')) {
            // Supprimer l'ancienne miniature
            if ($series->thumbnail_url) {
                Storage::delete('public/thumbnails/' . $series->thumbnail_url);
            }
            $thumbnailPath = $request->file('thumbnail')->store('public/thumbnails');
            $series->thumbnail_url = basename($thumbnailPath);
        }

        if ($request->hasFile('banner')) {
            // Supprimer l'ancienne bannière
            if ($series->banner_url) {
                Storage::delete('public/banners/' . $series->banner_url);
            }
            $bannerPath = $request->file('banner')->store('public/banners');
            $series->banner_url = basename($bannerPath);
        }

        $series->fill($request->except(['thumbnail', 'banner']));
        $series->save();

        return response()->json([
            'message' => 'Série mise à jour avec succès',
            'series' => $series->load('seasons'),
        ]);
    }

    public function destroy($id)
    {
        $series = Series::findOrFail($id);

        // Supprimer les fichiers
        if ($series->thumbnail_url) {
            Storage::delete('public/thumbnails/' . $series->thumbnail_url);
        }
        if ($series->banner_url) {
            Storage::delete('public/banners/' . $series->banner_url);
        }

        $series->delete();

        return response()->json([
            'message' => 'Série supprimée avec succès',
        ]);
    }

    public function publish($id)
    {
        $series = Series::findOrFail($id);

        $series->is_published = !$series->is_published;
        $series->save();

        return response()->json([
            'message' => $series->is_published ? 'Série publiée' : 'Série dépubliée',
            'series' => $series,
        ]);
    }

    /**
     * Mettre à jour uniquement la bannière d'une série
     */
    public function updateBanner(Request $request, $id)
    {
        $series = Series::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'banner' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('banner')) {
            // Supprimer l'ancienne bannière
            if ($series->banner_url) {
                Storage::delete('public/banners/' . $series->banner_url);
            }

            $bannerPath = $request->file('banner')->store('banners', 'public');
            $series->banner_url = basename($bannerPath);
            $series->save();
        }

        return response()->json([
            'message' => 'Bannière mise à jour avec succès',
            'series' => $series->load('seasons'),
            'banner_url' => $series->banner_url ? Storage::url('banners/' . $series->banner_url) : null,
        ]);
    }

    /**
     * Mettre à jour uniquement la miniature d'une série
     */
    public function updateThumbnail(Request $request, $id)
    {
        $series = Series::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'thumbnail' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->hasFile('thumbnail')) {
            // Supprimer l'ancienne miniature
            if ($series->thumbnail_url) {
                Storage::delete('public/thumbnails/' . $series->thumbnail_url);
            }

            $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
            $series->thumbnail_url = basename($thumbnailPath);
            $series->save();
        }

        return response()->json([
            'message' => 'Miniature mise à jour avec succès',
            'series' => $series->load('seasons'),
            'thumbnail_url' => $series->thumbnail_url ? Storage::url('thumbnails/' . $series->thumbnail_url) : null,
        ]);
    }
}

