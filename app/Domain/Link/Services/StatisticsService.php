<?php

namespace App\Domain\Link\Services;

use App\Domain\Link\Models\Link;
use Illuminate\Support\Facades\DB;

/**
 * StatisticsService
 * 
 * Handles analytics and statistics for links
 */
class StatisticsService
{
    /**
     * Get comprehensive statistics for a link
     * 
     * @param Link $link The link to get statistics for
     * @return array Statistics data including visits, devices, browsers, etc
     */
    public function getStatistics(Link $link): array
    {
        return [
            'total_visits' => $this->getTotalVisits($link),
            'unique_visitors' => $this->getUniqueVisitors($link),
            'visits_by_day' => $this->getVisitsByDay($link),
        ];
    }

    /**
     * Get total visit count
     * 
     * @param Link $link The link
     * @return int Total number of visits
     */
    public function getTotalVisits(Link $link): int
    {
        return $link->visits()->count();
    }

    /**
     * Get unique visitor count (by IP)
     * 
     * @param Link $link The link
     * @return int Number of unique visitors
     */
    public function getUniqueVisitors(Link $link): int
    {
        return $link->visits()
            ->distinct('ip_address')
            ->count('ip_address');
    }

    /**
     * Get visits grouped by day
     * 
     * @param Link $link The link
     * @param int $days Number of days to retrieve (default 30)
     * @return array Visits per day
     */
    public function getVisitsByDay(Link $link, int $days = 30): array
    {
        return $link->visits()
            ->select(
                DB::raw('DATE(visited_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('visited_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get()
            ->toArray();
    }
}
