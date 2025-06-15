<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Series;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeriesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $series = Series::withCount('comics')->orderBy('title', 'asc')->get();
        
        // Add favorite status for each series
        $userId = $request->user()->id;
        $series->each(function($item) use ($userId) {
            $item->is_favorite = $item->isFavoritedBy($userId);
        });
        
        return response()->json([
            'success' => true,
            'data' => $series
        ]);
    }

    public function show(Request $request, $id): JsonResponse
    {
        $series = Series::with(['comics' => function($query) {
            $query->orderBy('chapter_number', 'asc');
        }])->find($id);
        
        if (!$series) {
            return response()->json([
                'success' => false,
                'message' => 'Series not found'
            ], 404);
        }

        // Add favorite status
        $series->is_favorite = $series->isFavoritedBy($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $series
        ]);
    }
}