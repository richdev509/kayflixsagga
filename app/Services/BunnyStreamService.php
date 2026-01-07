<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BunnyStreamService
{
    protected $apiKey;
    protected $libraryId;
    protected $apiUrl;
    protected $cdnHostname;

    public function __construct()
    {
        $this->apiKey = config('bunny.api_key');
        $this->libraryId = config('bunny.stream.library_id');
        $this->apiUrl = config('bunny.api_url');
        $this->cdnHostname = config('bunny.stream.cdn_hostname');
    }

    /**
     * Create a new video in Bunny Stream
     */
    public function createVideo(string $title, ?int $collectionId = null)
    {
        try {
            $response = Http::withHeaders([
                'AccessKey' => $this->apiKey,
                'Accept' => 'application/json',
            ])->post("{$this->apiUrl}/library/{$this->libraryId}/videos", [
                'title' => $title,
                'collectionId' => $collectionId,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Bunny Stream create video failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Bunny Stream create video exception', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get upload URL for direct upload from client
     */
    public function getUploadUrl(string $videoId)
    {
        return "{$this->apiUrl}/library/{$this->libraryId}/videos/{$videoId}";
    }

    /**
     * Upload video from server
     */
    public function uploadVideo(string $videoId, $filePath)
    {
        try {
            // Bunny.net nécessite un PUT direct du fichier binaire
            $fileHandle = fopen($filePath, 'r');

            if (!$fileHandle) {
                Log::error('Bunny Stream upload failed: Cannot open file', [
                    'file' => $filePath,
                ]);
                return false;
            }

            $fileSize = filesize($filePath);

            $ch = curl_init();

            curl_setopt_array($ch, [
                CURLOPT_URL => "{$this->apiUrl}/library/{$this->libraryId}/videos/{$videoId}",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_HTTPHEADER => [
                    'AccessKey: ' . $this->apiKey,
                    'Content-Type: application/octet-stream',
                    'Content-Length: ' . $fileSize,
                ],
                CURLOPT_INFILE => $fileHandle,
                CURLOPT_INFILESIZE => $fileSize,
                CURLOPT_UPLOAD => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_TIMEOUT => 7200, // 2 heures pour gros fichiers
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);

            curl_close($ch);
            fclose($fileHandle);

            if ($error) {
                Log::error('Bunny Stream upload failed: cURL error', [
                    'error' => $error,
                    'videoId' => $videoId,
                ]);
                return false;
            }

            if ($httpCode >= 200 && $httpCode < 300) {
                Log::info('Bunny Stream upload successful', [
                    'videoId' => $videoId,
                    'httpCode' => $httpCode,
                ]);
                return true;
            }

            Log::error('Bunny Stream upload failed: HTTP error', [
                'httpCode' => $httpCode,
                'response' => $response,
                'videoId' => $videoId,
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Bunny Stream upload failed: Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Get video details
     */
    public function getVideo(string $videoId)
    {
        try {
            $response = Http::withHeaders([
                'AccessKey' => $this->apiKey,
                'Accept' => 'application/json',
            ])->get("{$this->apiUrl}/library/{$this->libraryId}/videos/{$videoId}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Bunny Stream get video failed', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Delete video
     */
    public function deleteVideo(string $videoId)
    {
        try {
            $response = Http::withHeaders([
                'AccessKey' => $this->apiKey,
            ])->delete("{$this->apiUrl}/library/{$this->libraryId}/videos/{$videoId}");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Bunny Stream delete video failed', [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get video stream URL for playback
     */
    public function getStreamUrl(string $videoId, bool $withToken = true)
    {
        $baseUrl = "https://{$this->cdnHostname}/{$videoId}/playlist.m3u8";

        if ($withToken) {
            // Generate signed URL with expiration (optional)
            $expiration = now()->addHours(24)->timestamp;
            $token = $this->generateToken($videoId, $expiration);
            return "{$baseUrl}?token={$token}&expires={$expiration}";
        }

        return $baseUrl;
    }

    /**
     * Get embed iframe URL
     */
    public function getEmbedUrl(string $videoId)
    {
        return config('bunny.stream_url') . "/embed/{$this->libraryId}/{$videoId}";
    }

    /**
     * Generate signed token for video playback
     */
    protected function generateToken(string $videoId, int $expiration)
    {
        // Simple token generation - customize based on Bunny's requirements
        return hash_hmac('sha256', "{$videoId}:{$expiration}", $this->apiKey);
    }

    /**
     * Get video statistics
     */
    public function getVideoStatistics(string $videoId)
    {
        try {
            $response = Http::withHeaders([
                'AccessKey' => $this->apiKey,
                'Accept' => 'application/json',
            ])->get("{$this->apiUrl}/library/{$this->libraryId}/statistics/videos/{$videoId}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Bunny Stream get statistics failed', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Update video metadata
     */
    public function updateVideo(string $videoId, array $data)
    {
        try {
            $response = Http::withHeaders([
                'AccessKey' => $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/library/{$this->libraryId}/videos/{$videoId}", $data);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Bunny Stream update video failed', [
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get video thumbnail URL
     */
    public function getThumbnailUrl(string $videoId)
    {
        return "https://{$this->cdnHostname}/{$videoId}/thumbnail.jpg";
    }

    /**
     * Generate signed token for video playback
     */
    public function generateSignedUrl(string $videoId, int $expirationTime = 3600)
    {
        $tokenKey = config('bunny.stream.token_key');

        if (!$tokenKey) {
            // Si pas de token key, retourner l'URL sans signature
            return "https://{$this->cdnHostname}/{$videoId}/playlist.m3u8";
        }

        // Timestamp d'expiration (timestamp actuel + durée en secondes)
        $expires = time() + $expirationTime;

        // Construire le token selon la documentation Bunny.net
        // SHA256_HEX(token_security_key + video_id + expiration)
        $hashString = $tokenKey . $videoId . $expires;
        $token = hash('sha256', $hashString);

        // Retourner l'URL signée
        return "https://{$this->cdnHostname}/{$videoId}/playlist.m3u8?token={$token}&expires={$expires}";
    }
}
