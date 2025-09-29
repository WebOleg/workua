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
     * Gets comprehensive statistics for a link
     * 
     * @param Link $link
     * @return array Statistics data
     */
    public function getStatistics(Link $link): array
    {
        return [
            'total_visits' => $this->getTotalVisits($link),
            'unique_visitors' => $this->getUniqueVisitors($link),
            'visits_by_day' => $this->getVisitsByDay($link),
            'visits_by_country' => $this->getVisitsByCountry($link),
            'visits_by_device' => $this->getVisitsByDevice($link),
            'visits_by_browser' => $this->getVisitsByBrowser($link),
            'top_referrers' => $this->getTopReferrers($link),
        ];
    }

    /**
     * Gets total visit count
     * 
     * @param Link $link
     * @return int
     */
    public function getTotalVisits(Link $link): int
    {
        return $link->visits()->count();
    }

    /**
     * Gets unique visitor count (by IP)
     * 
     * @param Link $link
     * @return int
     */
    public function getUniqueVisitors(Link $link): int
    {
        return $link->visits()
            ->distinct('ip_address')
            ->count('ip_address');
    }

    /**
     * Gets visits grouped by day
     * 
     * @param Link $link
     * @param int $days Number of days to retrieve (default 30)
     * @return array
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

    /**
     * Gets visits grouped by country
     * 
     * @param Link $link
     * @param int $limit Number of countries to return
     * @return array
     */
    public function getVisitsByCountry(Link $link, int $limit = 10): array
    {
        return $link->visits()
            ->select('country', DB::raw('COUNT(*) as count'))
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Gets visits grouped by device type
     * 
     * @param Link $link
     * @return array
     */
    public function getVisitsByDevice(Link $link): array
    {
        return $link->visits()
            ->select('device_type', DB::raw('COUNT(*) as count'))
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->orderBy('count', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Gets visits grouped by browser
     * 
     * @param Link $link
     * @param int $limit Number of browsers to return
     * @return array
     */
    public function getVisitsByBrowser(Link $link, int $limit = 10): array
    {
        return $link->visits()
            ->select('browser', DB::raw('COUNT(*) as count'))
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Gets top referring URLs
     * 
     * @param Link $link
     * @param int $limit Number of referrers to return
     * @return array
     */
    public function getTopReferrers(Link $link, int $limit = 10): array
    {
        return $link->visits()
            ->select('referer', DB::raw('COUNT(*) as count'))
            ->whereNotNull('referer')
            ->where('referer', '!=', '')
            ->groupBy('referer')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
