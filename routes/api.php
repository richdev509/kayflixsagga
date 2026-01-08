<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\VideoController;
use App\Http\Controllers\API\CreatorController;
use App\Http\Controllers\API\SubscriptionController;
use App\Http\Controllers\API\ThumbnailController;
use App\Http\Controllers\API\SeriesController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\StripeWebhookController;
use App\Http\Controllers\API\BannerController;
use App\Http\Controllers\API\ViewAnalyticController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public subscription plans
Route::get('/subscription-plans', [SubscriptionController::class, 'plans']);

// Payment routes (public for registration)
Route::post('/payment/create-checkout-session', [PaymentController::class, 'createCheckoutSession']);
Route::post('/payment/verify-session', [PaymentController::class, 'verifySession']);
Route::post('/payment/cancel-session', [PaymentController::class, 'cancelSession']);

// Stripe webhook (must be outside auth middleware)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook']);

// Serve thumbnails (public, with CORS)
Route::get('/thumbnails/{filename}', [ThumbnailController::class, 'show']);
Route::get('/banners/{filename}', [ThumbnailController::class, 'show']);

// Public banners pour l'écran de connexion
Route::get('/banners', [BannerController::class, 'index']);

// Public videos and series (pour l'écran de connexion)
Route::get('/public/videos', [VideoController::class, 'publicIndex']);
Route::get('/public/series', [SeriesController::class, 'publicIndex']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Videos
    Route::get('/videos', [VideoController::class, 'index']);
    Route::get('/videos/{id}', [VideoController::class, 'show']);
    Route::get('/videos/{id}/play', [VideoController::class, 'play']);
    Route::post('/videos/{id}/watch', [VideoController::class, 'logWatch']);
    Route::post('/videos/{id}/increment-views', [VideoController::class, 'incrementViews']);
    Route::post('/videos', [VideoController::class, 'store']);

    // Series
    Route::get('/series', [SeriesController::class, 'index']);
    Route::get('/series/search', [SeriesController::class, 'search']);
    Route::get('/series/category/{category}', [SeriesController::class, 'byCategory']);
    Route::get('/series/{id}', [SeriesController::class, 'show']);
    Route::get('/series/{seriesId}/seasons/{seasonId}', [SeriesController::class, 'getSeasonEpisodes']);
    Route::get('/series/{seriesId}/seasons/{seasonId}/episodes/{episodeId}', [SeriesController::class, 'getEpisode']);

    // Creator routes
    Route::post('/creator/apply', [CreatorController::class, 'apply']);
    Route::get('/creator/revenue', [CreatorController::class, 'revenue']);
    Route::post('/creator/upload', [CreatorController::class, 'uploadVideo']);

    // Subscription routes
    Route::post('/subscribe', [SubscriptionController::class, 'subscribe']);
    Route::get('/subscription/status', [SubscriptionController::class, 'status']);

    // View Analytics routes
    Route::post('/analytics/view/start', [ViewAnalyticController::class, 'start']);
    Route::put('/analytics/view/{sessionId}', [ViewAnalyticController::class, 'update']);
    Route::post('/analytics/view/{sessionId}/end', [ViewAnalyticController::class, 'end']);
    Route::get('/analytics/creator/{creatorId}/stats', [ViewAnalyticController::class, 'creatorStats']);
    Route::get('/analytics/video/{videoId}/stats', [ViewAnalyticController::class, 'videoStats']);
    Route::get('/analytics/series/{seriesId}/stats', [ViewAnalyticController::class, 'seriesStats']);
    Route::get('/analytics/episode/{episodeId}/stats', [ViewAnalyticController::class, 'episodeStats']);
});
