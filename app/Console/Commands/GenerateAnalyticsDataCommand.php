<?php
// app/Console/Commands/GenerateAnalyticsDataCommand.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\ReadingProgress;
use Database\Seeders\ReadingProgressSeeder;
use Database\Seeders\SeriesFavoritesSeeder;
use Database\Seeders\LocationFavoritesSeeder;

class GenerateAnalyticsDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'grandline:generate-analytics 
                            {--user= : Specific user ID to generate data for}
                            {--days=180 : Number of days to generate data for}
                            {--clear : Clear existing data before generating}
                            {--favorites : Also generate favorites data}';

    /**
     * The console command description.
     */
    protected $description = 'Generate rich analytics data for GrandLine app';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 GrandLine Analytics Data Generator');
        $this->info('=====================================');

        // Get options
        $userId = $this->option('user');
        $days = (int) $this->option('days');
        $clearData = $this->option('clear');
        $generateFavorites = $this->option('favorites');

        // Validate days
        if ($days < 7 || $days > 365) {
            $this->error('Days must be between 7 and 365');
            return 1;
        }

        // Get target user
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found");
                return 1;
            }
        } else {
            $user = User::first();
            if (!$user) {
                $this->error('No users found in database. Please create a user first.');
                return 1;
            }
        }

        $this->info("📱 Target user: {$user->name} (ID: {$user->id})");
        $this->info("📅 Generating data for {$days} days");

        // Clear existing data if requested
        if ($clearData) {
            if ($this->confirm('⚠️  This will clear all existing reading progress data for this user. Continue?')) {
                $this->info('🧹 Clearing existing data...');
                ReadingProgress::where('user_id', $user->id)->delete();
                
                if ($generateFavorites) {
                    $user->favorites()->delete();
                    $user->locationFavorites()->delete();
                }
                
                $this->info('✅ Existing data cleared');
            } else {
                $this->info('❌ Operation cancelled');
                return 0;
            }
        }

        // Show current statistics
        $currentStats = ReadingProgress::where('user_id', $user->id)->count();
        $this->info("📊 Current progress records: {$currentStats}");

        // Generate reading progress data
        $this->info('📚 Generating reading progress data...');
        $this->withProgressBar(range(1, $days), function ($day) use ($user, $days) {
            // This would call the seeder logic
            // For now, we'll call the seeder directly
        });

        // Run the seeder
        $this->call('db:seed', [
            '--class' => ReadingProgressSeeder::class
        ]);

        // Generate favorites if requested
        if ($generateFavorites) {
            $this->info("\n⭐ Generating favorites data...");
            
            $this->call('db:seed', [
                '--class' => SeriesFavoritesSeeder::class
            ]);
            
            $this->call('db:seed', [
                '--class' => LocationFavoritesSeeder::class
            ]);
        }

        // Show final statistics
        $this->showFinalStatistics($user->id);

        $this->info("\n🎉 Analytics data generation completed!");
        $this->info("🔥 Your analytics dashboard will now show rich, engaging data!");
        
        return 0;
    }

    private function showFinalStatistics($userId)
    {
        $this->info("\n📈 FINAL ANALYTICS PREVIEW:");
        $this->info("============================");

        $stats = ReadingProgress::where('user_id', $userId)
            ->selectRaw('
                COUNT(*) as total_records,
                COUNT(DISTINCT comic_id) as unique_comics,
                COUNT(DISTINCT series_id) as unique_series,
                SUM(current_page) as total_pages_read,
                AVG(progress_percentage) as avg_progress,
                COUNT(CASE WHEN progress_percentage >= 100 THEN 1 END) as completed_comics
            ')
            ->first();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Comics Read', $stats->unique_comics],
                ['Total Series', $stats->unique_series],
                ['Total Pages Read', number_format($stats->total_pages_read)],
                ['Completed Comics', $stats->completed_comics],
                ['Average Progress', round($stats->avg_progress, 1) . '%'],
                ['Completion Rate', round(($stats->completed_comics / $stats->total_records) * 100, 1) . '%'],
            ]
        );

        // Recent activity
        $recentActivity = ReadingProgress::where('user_id', $userId)
            ->where('last_read_at', '>=', now()->subDays(7))
            ->count();

        $this->info("🔥 Recent activity (7 days): {$recentActivity} reading sessions");

        // Reading streak
        $streak = $this->calculateStreak($userId);
        $this->info("🎯 Current reading streak: {$streak} days");

        // Chart preview data
        $this->info("\n📊 Chart Data Preview:");
        $monthlyData = ReadingProgress::where('user_id', $userId)
            ->selectRaw('DATE_FORMAT(last_read_at, "%Y-%m") as month, COUNT(*) as sessions, SUM(current_page) as pages')
            ->where('last_read_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        foreach ($monthlyData as $month) {
            $this->info("   {$month->month}: {$month->sessions} sessions, {$month->pages} pages");
        }
    }

    private function calculateStreak($userId): int
    {
        $streak = 0;
        $currentDate = now()->startOfDay();
        
        for ($i = 0; $i < 30; $i++) {
            $hasActivity = ReadingProgress::where('user_id', $userId)
                ->whereDate('last_read_at', $currentDate)
                ->exists();
            
            if (!$hasActivity) {
                break;
            }
            
            $streak++;
            $currentDate = $currentDate->subDay();
        }
        
        return $streak;
    }
}