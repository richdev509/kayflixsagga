<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Season;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SeasonController extends Controller
{
    public function store(Request $request, $seriesId)
    {
        $series = Series::findOrFail($seriesId);

        $validator = Validator::make($request->all(), [
            'season_number' => 'required|integer|min:1',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'release_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Vérifier si la saison existe déjà
        $existingSeason = Season::where('series_id', $seriesId)
            ->where('season_number', $request->season_number)
            ->first();

        if ($existingSeason) {
            return response()->json([
                'error' => 'Cette saison existe déjà pour cette série',
            ], 422);
        }

        $thumbnailUrl = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
            $thumbnailUrl = basename($thumbnailPath);
        }

        $season = Season::create([
            'series_id' => $seriesId,
            'season_number' => $request->season_number,
            'title' => $request->title ?? "Saison {$request->season_number}",
            'description' => $request->description,
            'release_year' => $request->release_year,
            'thumbnail_url' => $thumbnailUrl,
        ]);

        // Mettre à jour le nombre total de saisons
        $series->total_seasons = $series->seasons()->count();
        $series->save();

        return response()->json([
            'message' => 'Saison créée avec succès',
            'season' => $season->load('episodes'),
        ], 201);
    }

    public function update(Request $request, $seriesId, $id)
    {
        $series = Series::findOrFail($seriesId);

        $season = Season::where('series_id', $seriesId)
            ->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'season_number' => 'sometimes|required|integer|min:1',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'release_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 5),
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Vérifier l'unicité du numéro de saison
        if ($request->has('season_number')) {
            $existingSeason = Season::where('series_id', $seriesId)
                ->where('season_number', $request->season_number)
                ->where('id', '!=', $id)
                ->first();

            if ($existingSeason) {
                return response()->json([
                    'error' => 'Ce numéro de saison existe déjà pour cette série',
                ], 422);
            }
        }

        if ($request->hasFile('thumbnail')) {
            if ($season->thumbnail_url) {
                Storage::delete('public/thumbnails/' . $season->thumbnail_url);
            }
            $thumbnailPath = $request->file('thumbnail')->store('public/thumbnails');
            $season->thumbnail_url = basename($thumbnailPath);
        }

        $season->fill($request->except(['thumbnail']));
        $season->save();

        return response()->json([
            'message' => 'Saison mise à jour avec succès',
            'season' => $season->load('episodes'),
        ]);
    }

    public function destroy($seriesId, $id)
    {
        $series = Series::findOrFail($seriesId);

        $season = Season::where('series_id', $seriesId)
            ->findOrFail($id);

        if ($season->thumbnail_url) {
            Storage::delete('public/thumbnails/' . $season->thumbnail_url);
        }

        $season->delete();

        // Mettre à jour le nombre total de saisons
        $series->total_seasons = $series->seasons()->count();
        $series->save();

        return response()->json([
            'message' => 'Saison supprimée avec succès',
        ]);
    }
}

