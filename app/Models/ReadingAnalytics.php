<?php
// app/Models/ReadingAnalytics.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReadingAnalytics extends Model
{
    // Static methods untuk analytics data - tidak butuh table khusus
    
    /**
     * Get comprehensive reading statistics for user
     */
    public static function getUserStats($userId, $period = '30d')
    {
        $startDate = self::getStartDate($period);
        
        $baseQuery = ReadingProgress::where('user_id', $userId);
        $recentQuery = (clone $baseQuery)->where('last_read_at', '>=', $startDate);
        
        return [
            'overview' => [
                'total_comics_read' => $baseQuery->count(),
                'total_chapters_completed' => $baseQuery->where('progress_percentage', '>=', 100)->count(),
                'total_pages_read' => $baseQuery->sum('current_page'),
                'reading_streak' => self::getReadingStreak($userId),
                'average_progress' => round($baseQuery->avg('progress_percentage'), 1),
            ],
            'recent_activity' => [
                'comics_read_period' => $recentQuery->count(),
                'pages_read_period' => $recentQuery->sum('current_page'),
                'completion_rate' => self::getCompletionRate($userId, $period),
                'most_active_day' => self::getMostActiveDay($userId, $period),
            ],
            'reading_habits' => self::getReadingHabits($userId, $period),
            'genre_breakdown' => self::getGenreBreakdown($userId),
            'monthly_progress' => self::getMonthlyProgress($userId),
            'reading_velocity' => self::getReadingVelocity($userId, $period),
        ];
    }
    
    /**
     * Get daily reading activity for heatmap
     */
    public static function getReadingHeatmap($userId, $days = 365)
    {
        $startDate = Carbon::now()->subDays($days);
        
        $data = ReadingProgress::where('user_id', $userId)
            ->where('last_read_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(last_read_at) as date'),
                DB::raw('COUNT(*) as comics_read'),
                DB::raw('SUM(current_page) as pages_read')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Fill missing dates with 0 values
        $result = [];
        $current = $startDate->copy();
        
        while ($current <= Carbon::now()) {
            $dateStr = $current->format('Y-m-d');
            $dayData = $data->firstWhere('date', $dateStr);
            
            $result[] = [
                'date' => $dateStr,
                'comics_read' => $dayData ? $dayData->comics_read : 0,
                'pages_read' => $dayData ? $dayData->pages_read : 0,
                'intensity' => $dayData ? min(($dayData->pages_read / 50), 4) : 0, // Scale 0-4
            ];
            
            $current->addDay();
        }
        
        return $result;
    }
    
    /**
     * Get weekly reading pattern
     */
    public static function getWeeklyPattern($userId, $weeks = 12)
    {
        $startDate = Carbon::now()->subWeeks($weeks);
        
        return ReadingProgress::where('user_id', $userId)
            ->where('last_read_at', '>=', $startDate)
            ->select(
                DB::raw('DAYOFWEEK(last_read_at) as day_of_week'),
                DB::raw('COUNT(*) as comics_read'),
                DB::raw('SUM(current_page) as pages_read'),
                DB::raw('AVG(current_page) as avg_pages')
            )
            ->groupBy('day_of_week')
            ->orderBy('day_of_week')
            ->get()
            ->map(function ($item) {
                $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                return [
                    'day' => $days[$item->day_of_week - 1],
                    'day_short' => substr($days[$item->day_of_week - 1], 0, 3),
                    'comics_read' => $item->comics_read,
                    'pages_read' => $item->pages_read,
                    'avg_pages' => round($item->avg_pages, 1),
                ];
            });
    }
    
    /**
     * Get reading speed trends
     */
    public static function getReadingSpeedTrends($userId, $period = '30d')
    {
        $startDate = self::getStartDate($period);
        
        return ReadingProgress::where('user_id', $userId)
            ->where('last_read_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(last_read_at) as date'),
                DB::raw('COUNT(*) as sessions'),
                DB::raw('SUM(current_page) as total_pages'),
                DB::raw('AVG(current_page) as avg_pages_per_session')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'sessions' => $item->sessions,
                    'total_pages' => $item->total_pages,
                    'avg_pages_per_session' => round($item->avg_pages_per_session, 1),
                    'estimated_reading_time' => round($item->total_pages * 1.5, 0), // 1.5 min per page
                ];
            });
    }
    
    /**
     * Get top series by reading time
     */
    public static function getTopSeries($userId, $limit = 10)
    {
        return ReadingProgress::where('user_id', $userId)
            ->with(['series'])
            ->select(
                'series_id',
                DB::raw('COUNT(DISTINCT comic_id) as comics_read'),
                DB::raw('SUM(current_page) as total_pages'),
                DB::raw('AVG(progress_percentage) as avg_progress'),
                DB::raw('MAX(last_read_at) as last_read')
            )
            ->groupBy('series_id')
            ->orderBy('total_pages', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'series' => $item->series,
                    'comics_read' => $item->comics_read,
                    'total_pages' => $item->total_pages,
                    'avg_progress' => round($item->avg_progress, 1),
                    'last_read' => $item->last_read,
                    'estimated_time' => round($item->total_pages * 1.5, 0), // minutes
                ];
            });
    }
    
    /**
     * Helper: Get reading streak
     */
    private static function getReadingStreak($userId)
    {
        $streak = 0;
        $currentDate = Carbon::now()->startOfDay();
        
        while (true) {
            $hasActivity = ReadingProgress::where('user_id', $userId)
                ->whereDate('last_read_at', $currentDate)
                ->exists();
            
            if (!$hasActivity) {
                break;
            }
            
            $streak++;
            $currentDate = $currentDate->subDay();
            
            // Prevent infinite loop
            if ($streak > 365) break;
        }
        
        return $streak;
    }
    
    /**
     * Helper: Get completion rate
     */
    private static function getCompletionRate($userId, $period)
    {
        $startDate = self::getStartDate($period);
        
        $total = ReadingProgress::where('user_id', $userId)
            ->where('last_read_at', '>=', $startDate)
            ->count();
        
        $completed = ReadingProgress::where('user_id', $userId)
            ->where('last_read_at', '>=', $startDate)
            ->where('progress_percentage', '>=', 100)
            ->count();
        
        return $total > 0 ? round(($completed / $total) * 100, 1) : 0;
    }
    
    /**
     * Helper: Get most active day
     */
    private static function getMostActiveDay($userId, $period)
    {
        $startDate = self::getStartDate($period);
        
        $result = ReadingProgress::where('user_id', $userId)
            ->where('last_read_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(last_read_at) as date'),
                DB::raw('SUM(current_page) as pages_read')
            )
            ->groupBy('date')
            ->orderBy('pages_read', 'desc')
            ->first();
        
        return $result ? [
            'date' => $result->date,
            'pages_read' => $result->pages_read,
        ] : null;
    }
    
    /**
     * Helper: Get reading habits
     */
    private static function getReadingHabits($userId, $period)
    {
        $startDate = self::getStartDate($period);
        
        $hourlyData = ReadingProgress::where('user_id', $userId)
            ->where('last_read_at', '>=', $startDate)
            ->select(
                DB::raw('HOUR(last_read_at) as hour'),
                DB::raw('COUNT(*) as sessions'),
                DB::raw('SUM(current_page) as pages_read')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
        
        $peakHour = $hourlyData->sortByDesc('pages_read')->first();
        
        return [
            'peak_reading_hour' => $peakHour ? $peakHour->hour . ':00' : null,
            'total_sessions' => $hourlyData->sum('sessions'),
            'avg_session_length' => $hourlyData->count() > 0 ? 
                round($hourlyData->sum('pages_read') / $hourlyData->sum('sessions'), 1) : 0,
            'hourly_breakdown' => $hourlyData->toArray(),
        ];
    }
    
    /**
     * Helper: Get genre breakdown (based on series)
     */
    private static function getGenreBreakdown($userId)
    {
        // Simplified genre classification based on series title keywords
        $seriesData = ReadingProgress::where('user_id', $userId)
            ->with('series')
            ->select('series_id', DB::raw('SUM(current_page) as pages_read'))
            ->groupBy('series_id')
            ->get();
        
        $genres = [];
        foreach ($seriesData as $data) {
            if (!$data->series) continue;
            
            $title = strtolower($data->series->title);
            $genre = self::classifyGenre($title);
            
            if (!isset($genres[$genre])) {
                $genres[$genre] = ['pages_read' => 0, 'series_count' => 0];
            }
            
            $genres[$genre]['pages_read'] += $data->pages_read;
            $genres[$genre]['series_count']++;
        }
        
        return collect($genres)->map(function ($data, $genre) {
            return [
                'genre' => $genre,
                'pages_read' => $data['pages_read'],
                'series_count' => $data['series_count'],
            ];
        })->sortByDesc('pages_read')->values()->toArray();
    }
    
    /**
     * Helper: Get monthly progress
     */
    private static function getMonthlyProgress($userId)
    {
        return ReadingProgress::where('user_id', $userId)
            ->where('last_read_at', '>=', Carbon::now()->subMonths(12))
            ->select(
                DB::raw('DATE_FORMAT(last_read_at, "%Y-%m") as month'),
                DB::raw('COUNT(DISTINCT comic_id) as comics_read'),
                DB::raw('SUM(current_page) as pages_read'),
                DB::raw('COUNT(*) as sessions')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => $item->month,
                    'month_name' => Carbon::createFromFormat('Y-m', $item->month)->format('M Y'),
                    'comics_read' => $item->comics_read,
                    'pages_read' => $item->pages_read,
                    'sessions' => $item->sessions,
                ];
            });
    }
    
    /**
     * Helper: Get reading velocity (pages per day)
     */
    private static function getReadingVelocity($userId, $period)
    {
        $startDate = self::getStartDate($period);
        $days = Carbon::now()->diffInDays($startDate) + 1;
        
        $totalPages = ReadingProgress::where('user_id', $userId)
            ->where('last_read_at', '>=', $startDate)
            ->sum('current_page');
        
        return [
            'pages_per_day' => round($totalPages / $days, 1),
            'total_pages' => $totalPages,
            'period_days' => $days,
        ];
    }
    
    /**
     * Helper: Classify genre from title
     */
    private static function classifyGenre($title)
    {
        $genreKeywords = [
            'Action' => ['attack', 'fight', 'battle', 'war', 'titan', 'sword', 'ninja'],
            'Romance' => ['love', 'hana', 'romance', 'heart', 'kiss'],
            'Adventure' => ['piece', 'journey', 'adventure', 'quest', 'world'],
            'Comedy' => ['comedy', 'funny', 'laugh', 'humor'],
            'Fantasy' => ['magic', 'fantasy', 'dragon', 'power', 'leveling'],
            'Slice of Life' => ['daily', 'life', 'school', 'student'],
            'Thriller' => ['dark', 'shadow', 'death', 'mystery', 'secret'],
        ];
        
        foreach ($genreKeywords as $genre => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($title, $keyword) !== false) {
                    return $genre;
                }
            }
        }
        
        return 'Other';
    }
    
    /**
     * Helper: Get start date based on period
     */
    private static function getStartDate($period)
    {
        switch ($period) {
            case '7d':
                return Carbon::now()->subDays(7);
            case '30d':
                return Carbon::now()->subDays(30);
            case '90d':
                return Carbon::now()->subDays(90);
            case '1y':
                return Carbon::now()->subYear();
            default:
                return Carbon::now()->subDays(30);
        }
    }
}