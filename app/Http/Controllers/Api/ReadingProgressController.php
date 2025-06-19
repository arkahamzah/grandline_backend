<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReadingProgress;
use App\Models\Comic;
use Illuminate\Http\Request;

class ReadingProgressController extends Controller
{
    public function index(Request $request)
    {
        $progress = ReadingProgress::with(['comic.series', 'series'])
            ->where('user_id', $request->user()->id)
            ->orderBy('last_read_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $progress]);
    }

    public function continueReading(Request $request)
    {
        $continueReading = ReadingProgress::with(['comic.series', 'series'])
            ->where('user_id', $request->user()->id)
            ->where('progress_percentage', '<', 100)
            ->orderBy('last_read_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json(['success' => true, 'data' => $continueReading]);
    }

    public function updateProgress(Request $request, $comicId)
    {
        $request->validate(['current_page' => 'required|integer|min:0']);

        $comic = Comic::findOrFail($comicId);
        $user = $request->user();

        $progress = ReadingProgress::updateOrCreate(
            ['user_id' => $user->id, 'comic_id' => $comic->id],
            [
                'series_id' => $comic->series_id,
                'current_page' => (int) $request->current_page,
                'total_pages' => (int) $comic->page_count,
                'progress_percentage' => round(($request->current_page / $comic->page_count) * 100, 2),
                'last_read_at' => now(),
            ]
        );

        return response()->json(['success' => true, 'data' => $progress->load(['comic.series', 'series'])]);
    }

    public function getProgress(Request $request, $comicId)
    {
        $progress = ReadingProgress::with(['comic.series', 'series'])
            ->where('user_id', $request->user()->id)
            ->where('comic_id', $comicId)
            ->first();

        return response()->json(['success' => true, 'data' => $progress]);
    }

    public function getStats(Request $request)
    {
        $userId = $request->user()->id;
        
        $stats = [
            'total_read' => (int) ReadingProgress::where('user_id', $userId)->count(),
            'completed' => (int) ReadingProgress::where('user_id', $userId)->where('progress_percentage', '>=', 100)->count(),
            'in_progress' => (int) ReadingProgress::where('user_id', $userId)->where('progress_percentage', '>', 0)->where('progress_percentage', '<', 100)->count(),
            'total_pages_read' => (int) ReadingProgress::where('user_id', $userId)->sum('current_page'),
        ];

        return response()->json(['success' => true, 'data' => $stats]);
    }
}