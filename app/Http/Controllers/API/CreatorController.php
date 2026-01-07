<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Creator;
use App\Models\Video;
use App\Models\Payout;
use App\Services\BunnyStreamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreatorController extends Controller
{
    protected $bunnyService;

    public function __construct(BunnyStreamService $bunnyService)
    {
        $this->bunnyService = $bunnyService;
    }

    /**
     * Apply to become a creator
     */
    public function apply(Request $request)
    {
        $request->validate([
            'bio' => 'required|string|max:1000',
            'channel_name' => 'required|string|max:255|unique:creators',
        ]);

        $user = $request->user();

        // Check if already a creator
        if ($user->creator) {
            return response()->json([
                'message' => 'Vous avez déjà soumis une demande de créateur',
                'creator' => $user->creator,
            ], 400);
        }

        $creator = Creator::create([
            'user_id' => $user->id,
            'bio' => $request->bio,
            'channel_name' => $request->channel_name,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Demande de créateur soumise avec succès',
            'creator' => $creator,
        ], 201);
    }

    /**
     * Upload video to Bunny Stream
     */
    public function uploadVideo(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
        ]);

        $user = $request->user();
        $creator = $user->creator;

        if (!$creator || $creator->status !== 'approved') {
            return response()->json([
                'message' => 'Vous devez être un créateur approuvé pour uploader des vidéos',
            ], 403);
        }

        // Create video in Bunny Stream
        $bunnyVideo = $this->bunnyService->createVideo($request->title);

        if (!$bunnyVideo) {
            return response()->json([
                'message' => 'Erreur lors de la création de la vidéo sur Bunny Stream',
            ], 500);
        }

        // Save video in database
        $video = Video::create([
            'creator_id' => $creator->id,
            'vimeo_video_id' => $bunnyVideo['guid'], // Using Bunny video ID
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Vidéo créée avec succès',
            'video' => $video,
            'upload_url' => $this->bunnyService->getUploadUrl($bunnyVideo['guid']),
            'bunny_video_id' => $bunnyVideo['guid'],
        ], 201);
    }

    /**
     * Get creator revenue statistics
     */
    public function revenue(Request $request)
    {
        $user = $request->user();
        $creator = $user->creator;

        if (!$creator) {
            return response()->json([
                'message' => 'Vous n\'êtes pas un créateur',
            ], 403);
        }

        // Get payouts
        $payouts = Payout::where('creator_id', $creator->id)
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        // Get total earnings
        $totalEarnings = Payout::where('creator_id', $creator->id)
            ->where('status', 'paid')
            ->sum('amount');

        // Get pending earnings
        $pendingEarnings = Payout::where('creator_id', $creator->id)
            ->where('status', 'pending')
            ->sum('amount');

        // Get video statistics
        $totalVideos = Video::where('creator_id', $creator->id)->count();
        $totalViews = Video::where('creator_id', $creator->id)->sum('views_count');

        // Get watch time for current month
        $currentMonth = now()->format('Y-m');
        $currentMonthWatchTime = DB::table('watch_logs')
            ->join('videos', 'watch_logs.video_id', '=', 'videos.id')
            ->where('videos.creator_id', $creator->id)
            ->whereRaw('DATE_FORMAT(watch_logs.created_at, "%Y-%m") = ?', [$currentMonth])
            ->sum('watch_logs.seconds_watched');

        return response()->json([
            'payouts' => $payouts,
            'total_earnings' => $totalEarnings,
            'pending_earnings' => $pendingEarnings,
            'statistics' => [
                'total_videos' => $totalVideos,
                'total_views' => $totalViews,
                'current_month_watch_time_hours' => round($currentMonthWatchTime / 3600, 2),
            ],
        ]);
    }
}

