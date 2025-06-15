<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FavoriteController extends Controller
{
    // Get user's favorite series
    public function index(Request $request): JsonResponse
    {
        $favorites = Series::whereHas('favorites', function($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->withCount('comics')->orderBy('title', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $favorites
        ]);
    }

    // Toggle favorite status
    public function toggle(Request $request, $seriesId): JsonResponse
    {
        $user = $request->user();
        
        $favorite = Favorite::where('user_id', $user->id)
                           ->where('series_id', $seriesId)
                           ->first();

        if ($favorite) {
            // Remove from favorites
            $favorite->delete();
            $isFavorite = false;
            $message = 'Removed from favorites';
        } else {
            // Add to favorites
            Favorite::create([
                'user_id' => $user->id,
                'series_id' => $seriesId,
            ]);
            $isFavorite = true;
            $message = 'Added to favorites';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'is_favorite' => $isFavorite
        ]);
    }

    // Check if series is favorite
    public function check(Request $request, $seriesId): JsonResponse
    {
        $isFavorite = Favorite::where('user_id', $request->user()->id)
                             ->where('series_id', $seriesId)
                             ->exists();

        return response()->json([
            'success' => true,
            'is_favorite' => $isFavorite
        ]);
    }
}