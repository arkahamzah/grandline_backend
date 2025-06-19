<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SeriesController;
use App\Http\Controllers\Api\ComicController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\ReadingProgressController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\AnalyticsController; // ADD THIS

// Public routes (no authentication required)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Admin CRUD Routes - Tambahkan di routes/api.php
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    // Series CRUD
    Route::post('/series', [App\Http\Controllers\Api\SeriesController::class, 'store']);
    Route::put('/series/{id}', [App\Http\Controllers\Api\SeriesController::class, 'update']);
    Route::delete('/series/{id}', [App\Http\Controllers\Api\SeriesController::class, 'destroy']);
    
    // Comics CRUD
    Route::post('/comics', [App\Http\Controllers\Api\ComicController::class, 'store']);
    Route::put('/comics/{id}', [App\Http\Controllers\Api\ComicController::class, 'update']);
    Route::delete('/comics/{id}', [App\Http\Controllers\Api\ComicController::class, 'destroy']);
    
    // Users list
    Route::get('/users', [App\Http\Controllers\Api\AuthController::class, 'getAllUsers']);
});

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/profile/image', [AuthController::class, 'updateProfileImage']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // Series routes
    Route::get('/series', [SeriesController::class, 'index']);
    Route::get('/series/{id}', [SeriesController::class, 'show']);
    Route::get('/series/{id}/comics', [ComicController::class, 'getBySeriesId']);
    
    // Comics routes
    Route::get('/comics', [ComicController::class, 'index']);
    Route::get('/comics/{id}', [ComicController::class, 'show']);

    // Favorites routes
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{seriesId}/toggle', [FavoriteController::class, 'toggle']);
    Route::get('/favorites/{seriesId}/check', [FavoriteController::class, 'check']);
    
    // Reading Progress routes
    Route::get('/reading-progress', [ReadingProgressController::class, 'index']);
    Route::get('/reading-progress/continue', [ReadingProgressController::class, 'continueReading']);
    Route::get('/reading-progress/stats', [ReadingProgressController::class, 'getStats']);
    Route::post('/reading-progress/{comicId}', [ReadingProgressController::class, 'updateProgress']);
    Route::get('/reading-progress/{comicId}', [ReadingProgressController::class, 'getProgress']);
    
    // Location routes
    Route::prefix('locations')->group(function () {
        Route::get('/', [LocationController::class, 'index']);
        Route::get('/categories', [LocationController::class, 'categories']);
        Route::get('/nearby', [LocationController::class, 'nearby']);
        Route::get('/search', [LocationController::class, 'search']);
        Route::get('/favorites', [LocationController::class, 'favorites']);
        Route::get('/stats', [LocationController::class, 'stats']);
        Route::get('/{id}', [LocationController::class, 'show']);
        Route::post('/{id}/favorite', [LocationController::class, 'toggleFavorite']);
    });
    
    // 🔥 NEW: Analytics routes
    Route::prefix('analytics')->group(function () {
        // Main dashboard with comprehensive stats
        Route::get('/dashboard', [AnalyticsController::class, 'getDashboard']);
        
        // Reading activity heatmap (calendar view)
        Route::get('/heatmap', [AnalyticsController::class, 'getHeatmap']);
        
        // Weekly reading patterns analysis
        Route::get('/weekly-pattern', [AnalyticsController::class, 'getWeeklyPattern']);
        
        // Reading speed trends over time
        Route::get('/speed-trends', [AnalyticsController::class, 'getSpeedTrends']);
        
        // Top series by reading time/activity
        Route::get('/top-series', [AnalyticsController::class, 'getTopSeries']);
        
        // Reading goals and achievements
        Route::get('/goals', [AnalyticsController::class, 'getGoals']);
        
        // Cache management
        Route::delete('/cache', [AnalyticsController::class, 'clearCache']);
    });
    
    // User info route
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    });
});

/*
📊 ANALYTICS API ENDPOINTS DOCUMENTATION:

🎯 Main Dashboard:
GET /api/analytics/dashboard?period=30d
- Comprehensive reading statistics
- Parameters: period (7d, 30d, 90d, 1y)
- Returns: overview, recent_activity, reading_habits, genre_breakdown, etc.

🔥 Activity Heatmap:
GET /api/analytics/heatmap?days=365
- Daily reading activity for calendar heatmap
- Parameters: days (30-365)
- Returns: heatmap data + summary stats

📅 Weekly Patterns:
GET /api/analytics/weekly-pattern?weeks=12
- Reading patterns by day of week
- Parameters: weeks (4-52)
- Returns: weekly breakdown + insights (best/worst days)

🚀 Speed Trends:
GET /api/analytics/speed-trends?period=30d
- Reading speed analysis over time
- Parameters: period (7d, 30d, 90d)
- Returns: daily trends + improvement analysis

🏆 Top Series:
GET /api/analytics/top-series?limit=10
- Most read series ranking
- Parameters: limit (5-20)
- Returns: series ranking + reading time stats

🎯 Goals & Achievements:
GET /api/analytics/goals
- Reading goals progress + unlocked achievements
- No parameters
- Returns: goals progress + achievement list

🧹 Cache Management:
DELETE /api/analytics/cache
- Clear user's analytics cache
- No parameters
- Returns: success confirmation

EXAMPLE USAGE:
curl -H "Authorization: Bearer YOUR_TOKEN" \
     "https://yourapp.com/api/analytics/dashboard?period=30d"

curl -H "Authorization: Bearer YOUR_TOKEN" \
     "https://yourapp.com/api/analytics/heatmap?days=365"

RESPONSE FORMAT:
{
  "success": true,
  "data": { ... analytics data ... },
  "period": "30d",
  "generated_at": "2025-06-16T10:30:00Z"
}
*/