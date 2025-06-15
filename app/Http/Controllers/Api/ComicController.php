<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comic;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ComicController extends Controller
{
    public function index(): JsonResponse
    {
        $comics = Comic::with('series')
                      ->orderBy('series_id', 'asc')
                      ->orderBy('chapter_number', 'asc')
                      ->get();
        
        return response()->json([
            'success' => true,
            'data' => $comics
        ]);
    }

    public function show($id): JsonResponse
    {
        $comic = Comic::with('series')->find($id);
        
        if (!$comic) {
            return response()->json([
                'success' => false,
                'message' => 'Comic not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $comic
        ]);
    }

    public function getBySeriesId($seriesId): JsonResponse
    {
        $comics = Comic::where('series_id', $seriesId)
                      ->orderBy('chapter_number', 'asc')
                      ->get();
        
        return response()->json([
            'success' => true,
            'data' => $comics
        ]);
    }
}