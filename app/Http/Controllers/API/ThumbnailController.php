<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ThumbnailController extends Controller
{
    public function show($filename)
    {
        // Chercher d'abord dans thumbnails, puis dans banners
        $thumbnailPath = storage_path("app/public/thumbnails/{$filename}");
        $bannerPath = storage_path("app/public/banners/{$filename}");
        
        $path = null;
        if (file_exists($thumbnailPath)) {
            $path = $thumbnailPath;
        } elseif (file_exists($bannerPath)) {
            $path = $bannerPath;
        }
        
        if (!$path) {
            abort(404, "Image not found: {$filename}");
        }
        
        $mimeType = mime_content_type($path);
        
        return response()->file($path, [
            'Content-Type' => $mimeType,
            'Access-Control-Allow-Origin' => '*',
        ]);
    }
}
