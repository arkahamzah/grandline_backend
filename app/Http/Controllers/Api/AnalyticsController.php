<?php
// app/Http/Controllers/Api/AnalyticsController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReadingAnalytics;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    /**
     * Get comprehensive user reading analytics
     */
    public function getDashboard(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'period' => 'string|in:7d,30d,90d,1y'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid period parameter',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = $request->user()->id;
            $period = $request->get('period', '30d');
            
            // Cache analytics data for 30 minutes per user
            $cacheKey = "user_analytics_{$userId}_{$period}";
            
            $analytics = Cache::remember($cacheKey, 1800, function () use ($userId, $period) {
                return ReadingAnalytics::getUserStats($userId, $period);
            });

            return response()->json([
                'success' => true,
                'data' => $analytics,
                'period' => $period,
                'generated_at' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reading heatmap data for calendar view
     */
    public function getHeatmap(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'days' => 'integer|min:30|max:365'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid days parameter (30-365)',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = $request->user()->id;
            $days = $request->get('days', 365);
            
            $cacheKey = "heatmap_{$userId}_{$days}";
            
            $heatmapData = Cache::remember($cacheKey, 3600, function () use ($userId, $days) {
                return ReadingAnalytics::getReadingHeatmap($userId, $days);
            });

            // Calculate additional stats
            $totalDays = count($heatmapData);
            $activeDays = count(array_filter($heatmapData, fn($day) => $day['pages_read'] > 0));
            $totalPages = array_sum(array_column($heatmapData, 'pages_read'));
            $avgPagesPerDay = $totalPages > 0 ? round($totalPages / $totalDays, 1) : 0;
            $streak = $this->calculateCurrentStreak($heatmapData);

            return response()->json([
                'success' => true,
                'data' => [
                    'heatmap' => $heatmapData,
                    'summary' => [
                        'total_days' => $totalDays,
                        'active_days' => $activeDays,
                        'activity_rate' => round(($activeDays / $totalDays) * 100, 1),
                        'total_pages' => $totalPages,
                        'avg_pages_per_day' => $avgPagesPerDay,
                        'current_streak' => $streak,
                        'max_intensity' => max(array_column($heatmapData, 'intensity')),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load heatmap: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get weekly reading patterns
     */
    public function getWeeklyPattern(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'weeks' => 'integer|min:4|max:52'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid weeks parameter (4-52)',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = $request->user()->id;
            $weeks = $request->get('weeks', 12);
            
            $cacheKey = "weekly_pattern_{$userId}_{$weeks}";
            
            $weeklyData = Cache::remember($cacheKey, 1800, function () use ($userId, $weeks) {
                return ReadingAnalytics::getWeeklyPattern($userId, $weeks);
            });

            // Find best and worst days
            $bestDay = $weeklyData->sortByDesc('pages_read')->first();
            $worstDay = $weeklyData->sortBy('pages_read')->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'weekly_pattern' => $weeklyData->values(),
                    'insights' => [
                        'best_day' => $bestDay ? [
                            'day' => $bestDay['day'],
                            'pages_read' => $bestDay['pages_read'],
                            'avg_pages' => $bestDay['avg_pages']
                        ] : null,
                        'worst_day' => $worstDay ? [
                            'day' => $worstDay['day'],
                            'pages_read' => $worstDay['pages_read'],
                            'avg_pages' => $worstDay['avg_pages']
                        ] : null,
                        'total_pages' => $weeklyData->sum('pages_read'),
                        'most_consistent' => $weeklyData->sortByDesc('comics_read')->first()['day'] ?? null,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load weekly pattern: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reading speed trends
     */
    public function getSpeedTrends(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'period' => 'string|in:7d,30d,90d'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid period parameter',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = $request->user()->id;
            $period = $request->get('period', '30d');
            
            $cacheKey = "speed_trends_{$userId}_{$period}";
            
            $speedData = Cache::remember($cacheKey, 1800, function () use ($userId, $period) {
                return ReadingAnalytics::getReadingSpeedTrends($userId, $period);
            });

            // Calculate insights
            $avgSpeed = $speedData->avg('avg_pages_per_session');
            $maxSpeed = $speedData->max('avg_pages_per_session');
            $totalTime = $speedData->sum('estimated_reading_time');
            $improvementTrend = $this->calculateTrend($speedData->pluck('avg_pages_per_session')->toArray());

            return response()->json([
                'success' => true,
                'data' => [
                    'trends' => $speedData->values(),
                    'insights' => [
                        'avg_pages_per_session' => round($avgSpeed, 1),
                        'max_pages_per_session' => round($maxSpeed, 1),
                        'total_reading_time_minutes' => round($totalTime, 0),
                        'total_reading_time_hours' => round($totalTime / 60, 1),
                        'improvement_trend' => $improvementTrend, // 'improving', 'declining', 'stable'
                        'consistency_score' => $this->calculateConsistency($speedData->pluck('avg_pages_per_session')->toArray()),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load speed trends: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top series analytics
     */
    public function getTopSeries(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'limit' => 'integer|min:5|max:20'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid limit parameter (5-20)',
                    'errors' => $validator->errors()
                ], 422);
            }

            $userId = $request->user()->id;
            $limit = $request->get('limit', 10);
            
            $cacheKey = "top_series_{$userId}_{$limit}";
            
            $topSeries = Cache::remember($cacheKey, 1800, function () use ($userId, $limit) {
                return ReadingAnalytics::getTopSeries($userId, $limit);
            });

            // Calculate additional insights
            $totalTime = collect($topSeries)->sum('estimated_time');
            $avgProgress = collect($topSeries)->avg('avg_progress');
            $mostRecentSeries = collect($topSeries)->sortByDesc('last_read')->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'top_series' => $topSeries,
                    'insights' => [
                        'total_series' => count($topSeries),
                        'total_reading_time_hours' => round($totalTime / 60, 1),
                        'average_completion_rate' => round($avgProgress, 1),
                        'most_recent_series' => $mostRecentSeries ? [
                            'title' => $mostRecentSeries['series']->title,
                            'last_read' => $mostRecentSeries['last_read'],
                        ] : null,
                        'favorite_series' => collect($topSeries)->sortByDesc('total_pages')->first()['series']->title ?? null,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load top series: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reading goals and achievements
     */
    public function getGoals(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            
            $cacheKey = "reading_goals_{$userId}";
            
            $goalsData = Cache::remember($cacheKey, 900, function () use ($userId) {
                $stats = ReadingAnalytics::getUserStats($userId, '30d');
                
                // Define goals and check achievements
                $goals = [
                    'daily_pages' => [
                        'target' => 50,
                        'current' => $stats['reading_velocity']['pages_per_day'],
                        'progress' => min(($stats['reading_velocity']['pages_per_day'] / 50) * 100, 100),
                        'type' => 'daily'
                    ],
                    'weekly_comics' => [
                        'target' => 7,
                        'current' => $stats['recent_activity']['comics_read_period'] / 4, // weekly average
                        'progress' => min((($stats['recent_activity']['comics_read_period'] / 4) / 7) * 100, 100),
                        'type' => 'weekly'
                    ],
                    'monthly_completion' => [
                        'target' => 80, // 80% completion rate
                        'current' => $stats['recent_activity']['completion_rate'],
                        'progress' => min(($stats['recent_activity']['completion_rate'] / 80) * 100, 100),
                        'type' => 'monthly'
                    ],
                    'reading_streak' => [
                        'target' => 7,
                        'current' => $stats['overview']['reading_streak'],
                        'progress' => min(($stats['overview']['reading_streak'] / 7) * 100, 100),
                        'type' => 'streak'
                    ]
                ];

                // Calculate achievements
                $achievements = [];
                foreach ($goals as $key => $goal) {
                    if ($goal['progress'] >= 100) {
                        $achievements[] = [
                            'id' => $key,
                            'title' => $this->getAchievementTitle($key),
                            'description' => $this->getAchievementDescription($key, $goal['current']),
                            'unlocked_at' => now()->toISOString(),
                            'icon' => $this->getAchievementIcon($key)
                        ];
                    }
                }

                return compact('goals', 'achievements');
            });

            return response()->json([
                'success' => true,
                'data' => $goalsData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load goals: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear analytics cache for user
     */
    public function clearCache(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            
            $cacheKeys = [
                "user_analytics_{$userId}_7d",
                "user_analytics_{$userId}_30d",
                "user_analytics_{$userId}_90d",
                "user_analytics_{$userId}_1y",
                "heatmap_{$userId}_365",
                "weekly_pattern_{$userId}_12",
                "speed_trends_{$userId}_30d",
                "top_series_{$userId}_10",
                "reading_goals_{$userId}"
            ];

            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }

            return response()->json([
                'success' => true,
                'message' => 'Analytics cache cleared successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Calculate current streak from heatmap data
     */
    private function calculateCurrentStreak(array $heatmapData): int
    {
        $streak = 0;
        $reversedData = array_reverse($heatmapData);
        
        foreach ($reversedData as $day) {
            if ($day['pages_read'] > 0) {
                $streak++;
            } else {
                break;
            }
        }
        
        return $streak;
    }

    /**
     * Helper: Calculate trend direction
     */
    private function calculateTrend(array $values): string
    {
        if (count($values) < 2) return 'stable';
        
        $firstHalf = array_slice($values, 0, ceil(count($values) / 2));
        $secondHalf = array_slice($values, floor(count($values) / 2));
        
        $firstAvg = array_sum($firstHalf) / count($firstHalf);
        $secondAvg = array_sum($secondHalf) / count($secondHalf);
        
        $difference = $secondAvg - $firstAvg;
        
        if ($difference > 2) return 'improving';
        if ($difference < -2) return 'declining';
        return 'stable';
    }

    /**
     * Helper: Calculate consistency score
     */
    private function calculateConsistency(array $values): float
    {
        if (count($values) < 2) return 100;
        
        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $values)) / count($values);
        $stdDev = sqrt($variance);
        
        // Convert to consistency score (0-100, higher is more consistent)
        $consistencyScore = max(0, 100 - ($stdDev / $mean) * 100);
        
        return round($consistencyScore, 1);
    }

    /**
     * Helper: Get achievement titles
     */
    private function getAchievementTitle(string $key): string
    {
        $titles = [
            'daily_pages' => 'Page Turner',
            'weekly_comics' => 'Weekly Warrior',
            'monthly_completion' => 'Completion Master',
            'reading_streak' => 'Streak Champion'
        ];
        
        return $titles[$key] ?? 'Achievement Unlocked';
    }

    /**
     * Helper: Get achievement descriptions
     */
    private function getAchievementDescription(string $key, float $value): string
    {
        $descriptions = [
            'daily_pages' => "Read {$value} pages per day on average",
            'weekly_comics' => "Read {$value} comics per week",
            'monthly_completion' => "Achieved {$value}% completion rate",
            'reading_streak' => "Maintained {$value} day reading streak"
        ];
        
        return $descriptions[$key] ?? 'Achievement completed';
    }

    /**
     * Helper: Get achievement icons
     */
    private function getAchievementIcon(string $key): string
    {
        $icons = [
            'daily_pages' => '📖',
            'weekly_comics' => '⚡',
            'monthly_completion' => '🏆',
            'reading_streak' => '🔥'
        ];
        
        return $icons[$key] ?? '🎉';
    }
}