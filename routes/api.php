<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SeriesController;
use App\Http\Controllers\Api\ComicController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FavoriteController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
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
    
    // Comic routes
    Route::get('/comics', [ComicController::class, 'index']);
    Route::get('/comics/{id}', [ComicController::class, 'show']);

    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/{seriesId}/toggle', [FavoriteController::class, 'toggle']);
    Route::get('/favorites/{seriesId}/check', [FavoriteController::class, 'check']);
    
});