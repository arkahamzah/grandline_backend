<?php
// app/Http/Controllers/Api/LocationController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\LocationFavorite;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    // Get all locations with optional filters
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Location::active();
            
            // Filter by category
            if ($request->has('category') && $request->category !== 'All') {
                $query->byCategory($request->category);
            }
            
            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('address', 'LIKE', "%{$searchTerm}%");
                });
            }
            
            $locations = $query->orderBy('name')->get();
            
            // Add favorite status for authenticated user
            $userId = $request->user()->id;
            $locations->each(function($location) use ($userId) {
                $location->is_favorite = $location->isFavoritedBy($userId);
            });
            
            return response()->json([
                'success' => true,
                'data' => $locations
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load locations: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get location details
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $location = Location::active()->find($id);
            
            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location not found'
                ], 404);
            }
            
            // Add favorite status
            $location->is_favorite = $location->isFavoritedBy($request->user()->id);
            
            return response()->json([
                'success' => true,
                'data' => $location
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load location: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get nearby locations
    public function nearby(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.1|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coordinates',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $latitude = $request->lat;
            $longitude = $request->lng;
            $radius = $request->radius ?? 10; // Default 10km radius
            
            $locations = Location::active()
                ->nearby($latitude, $longitude, $radius)
                ->get();
            
            // Add favorite status for authenticated user
            $userId = $request->user()->id;
            $locations->each(function($location) use ($userId) {
                $location->is_favorite = $location->isFavoritedBy($userId);
            });
            
            return response()->json([
                'success' => true,
                'data' => $locations
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load nearby locations: ' . $e->getMessage()
            ], 500);
        }
    }

    // Search locations
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Search query must be at least 2 characters',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $searchTerm = $request->q;
            
            $locations = Location::active()
                ->where(function($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', "%{$searchTerm}%")
                          ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                          ->orWhere('address', 'LIKE', "%{$searchTerm}%")
                          ->orWhere('category', 'LIKE', "%{$searchTerm}%");
                })
                ->orderBy('name')
                ->get();
            
            // Add favorite status for authenticated user
            $userId = $request->user()->id;
            $locations->each(function($location) use ($userId) {
                $location->is_favorite = $location->isFavoritedBy($userId);
            });
            
            return response()->json([
                'success' => true,
                'data' => $locations
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    // Toggle favorite location
    public function toggleFavorite(Request $request, $locationId): JsonResponse
    {
        try {
            $location = Location::active()->find($locationId);
            
            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Location not found'
                ], 404);
            }
            
            $user = $request->user();
            
            $favorite = LocationFavorite::where('user_id', $user->id)
                                      ->where('location_id', $locationId)
                                      ->first();
            
            if ($favorite) {
                // Remove from favorites
                $favorite->delete();
                $isFavorite = false;
                $message = 'Removed from favorites';
            } else {
                // Add to favorites
                LocationFavorite::create([
                    'user_id' => $user->id,
                    'location_id' => $locationId,
                ]);
                $isFavorite = true;
                $message = 'Added to favorites';
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_favorite' => $isFavorite
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle favorite: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get user's favorite locations
    public function favorites(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            
            $favoriteLocations = Location::whereHas('favoritedBy', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })->active()->orderBy('name')->get();
            
            // Mark all as favorites since they're from favorites list
            $favoriteLocations->each(function($location) {
                $location->is_favorite = true;
            });
            
            return response()->json([
                'success' => true,
                'data' => $favoriteLocations
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load favorite locations: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get all available categories
    public function categories(): JsonResponse
    {
        try {
            $categories = Location::active()
                ->select('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category');
            
            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load categories: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get location statistics
    public function stats(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            
            $stats = [
                'total_locations' => Location::active()->count(),
                'total_favorites' => LocationFavorite::where('user_id', $userId)->count(),
                'categories' => Location::active()->select('category')->distinct()->count(),
                'category_breakdown' => Location::active()
                    ->selectRaw('category, COUNT(*) as count')
                    ->groupBy('category')
                    ->orderBy('count', 'desc')
                    ->get()
            ];
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}