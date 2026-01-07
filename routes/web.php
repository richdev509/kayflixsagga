<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\VideoController as AdminVideoController;
use App\Http\Controllers\Admin\SeriesController;
use App\Http\Controllers\Admin\SeasonController;
use App\Http\Controllers\Admin\EpisodeController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\RevenueController;
use App\Http\Controllers\Admin\AnalyticsController;

Route::get('/', function () {
    return view('welcome');
});

// Payment Success/Cancel Routes (for Stripe redirect)
Route::get('/payment/success', function () {
    return view('payment.success');
});

Route::get('/payment/cancel', function () {
    return view('payment.cancel');
});

// Authentication Routes
Auth::routes();

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Videos Management
    Route::get('/videos', [AdminVideoController::class, 'index'])->name('videos.index');
    Route::get('/videos/create', [AdminVideoController::class, 'create'])->name('videos.create');
    Route::post('/videos', [AdminVideoController::class, 'store'])->name('videos.store');
    Route::get('/videos/{video}', [AdminVideoController::class, 'show'])->name('videos.show');
    Route::get('/videos/{video}/edit', [AdminVideoController::class, 'edit'])->name('videos.edit');
    Route::put('/videos/{video}', [AdminVideoController::class, 'update'])->name('videos.update');
    Route::delete('/videos/{video}', [AdminVideoController::class, 'destroy'])->name('videos.destroy');
    Route::post('/videos/{video}/toggle-publish', [AdminVideoController::class, 'togglePublish'])->name('videos.toggle-publish');
    Route::post('/videos/{video}/refresh', [AdminVideoController::class, 'refreshFromBunny'])->name('videos.refresh');

    // Series Management
    Route::get('/series', [SeriesController::class, 'index'])->name('series.index');
    Route::post('/series', [SeriesController::class, 'store'])->name('series.store');
    Route::get('/series/{series}', [SeriesController::class, 'show'])->name('series.show');
    Route::put('/series/{series}', [SeriesController::class, 'update'])->name('series.update');
    Route::delete('/series/{series}', [SeriesController::class, 'destroy'])->name('series.destroy');
    Route::post('/series/{series}/publish', [SeriesController::class, 'publish'])->name('series.publish');
    Route::post('/series/{series}/banner', [SeriesController::class, 'updateBanner'])->name('series.update-banner');
    Route::post('/series/{series}/thumbnail', [SeriesController::class, 'updateThumbnail'])->name('series.update-thumbnail');

    // Seasons Management
    Route::post('/series/{series}/seasons', [SeasonController::class, 'store'])->name('seasons.store');
    Route::put('/series/{series}/seasons/{season}', [SeasonController::class, 'update'])->name('seasons.update');
    Route::delete('/series/{series}/seasons/{season}', [SeasonController::class, 'destroy'])->name('seasons.destroy');

    // Episodes Management
    Route::post('/series/{series}/seasons/{season}/episodes', [EpisodeController::class, 'store'])->name('episodes.store');
    Route::put('/series/{series}/seasons/{season}/episodes/{episode}', [EpisodeController::class, 'update'])->name('episodes.update');
    Route::delete('/series/{series}/seasons/{season}/episodes/{episode}', [EpisodeController::class, 'destroy'])->name('episodes.destroy');
    Route::post('/series/{series}/seasons/{season}/episodes/{episode}/publish', [EpisodeController::class, 'publish'])->name('episodes.publish');

    // Bunny Video Management for Episodes
    Route::post('/bunny/videos/create', [EpisodeController::class, 'createVideo'])->name('bunny.videos.create');
    Route::post('/bunny/videos/{videoId}/upload', [EpisodeController::class, 'uploadVideo'])->name('bunny.videos.upload');
    Route::get('/bunny/videos/{videoId}/status', [EpisodeController::class, 'getVideoStatus'])->name('bunny.videos.status');

    // Creators Management
    Route::get('/creators', [AdminController::class, 'creators'])->name('creators.index');
    Route::post('/creators/{creator}/approve', [AdminController::class, 'approveCreator'])->name('creators.approve');
    Route::delete('/creators/{creator}/reject', [AdminController::class, 'rejectCreator'])->name('creators.reject');

    // Users Management
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');

    // Revenue Distribution
    Route::get('/revenue', [RevenueController::class, 'index'])->name('revenue.index');
    Route::post('/revenue/distribute', [RevenueController::class, 'distribute'])->name('revenue.distribute');
    Route::post('/revenue/{payout}/mark-paid', [RevenueController::class, 'markAsPaid'])->name('revenue.mark-paid');
    Route::get('/revenue/creator/{creator}', [RevenueController::class, 'creatorDetails'])->name('revenue.creator-details');

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/video/{video}', [AnalyticsController::class, 'videoDetails'])->name('analytics.video');
    Route::get('/analytics/series/{series}', [AnalyticsController::class, 'seriesDetails'])->name('analytics.series');

    // Subscription Plans Management
    Route::resource('subscription-plans', SubscriptionPlanController::class);
    Route::patch('/subscription-plans/{subscriptionPlan}/toggle', [SubscriptionPlanController::class, 'toggle'])->name('subscription-plans.toggle');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
