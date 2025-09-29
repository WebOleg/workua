<?php

namespace App\Jobs;

use App\Domain\Link\Models\Link;
use App\Domain\Link\Models\LinkVisit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * RecordLinkVisitJob
 * 
 * Asynchronously records visit data for analytics
 */
class RecordLinkVisitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted
     * 
     * @var int
     */
    public $tries = 3;

    /**
     * Creates a new job instance
     * 
     * @param int $linkId
     * @param array $visitData
     */
    public function __construct(
        private readonly int $linkId,
        private readonly array $visitData
    ) {}

    /**
     * Execute the job
     * 
     * @return void
     */
    public function handle(): void
    {
        $link = Link::find($this->linkId);

        if (!$link) {
            return;
        }

        LinkVisit::create([
            'link_id' => $this->linkId,
            'ip_address' => $this->visitData['ip'] ?? null,
            'user_agent' => $this->visitData['user_agent'] ?? null,
            'referer' => $this->visitData['referer'] ?? null,
            'country' => $this->visitData['country'] ?? null,
            'city' => $this->visitData['city'] ?? null,
            'device_type' => $this->parseDeviceType($this->visitData['user_agent'] ?? ''),
            'browser' => $this->parseBrowser($this->visitData['user_agent'] ?? ''),
            'os' => $this->parseOS($this->visitData['user_agent'] ?? ''),
            'visited_at' => now(),
        ]);
    }

    /**
     * Parses device type from user agent
     * 
     * @param string $userAgent
     * @return string|null
     */
    private function parseDeviceType(string $userAgent): ?string
    {
        if (empty($userAgent)) {
            return null;
        }

        if (preg_match('/mobile|android|iphone|ipad|phone/i', $userAgent)) {
            return 'mobile';
        }

        if (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * Parses browser from user agent
     * 
     * @param string $userAgent
     * @return string|null
     */
    private function parseBrowser(string $userAgent): ?string
    {
        if (empty($userAgent)) {
            return null;
        }

        if (preg_match('/Chrome/i', $userAgent)) return 'Chrome';
        if (preg_match('/Firefox/i', $userAgent)) return 'Firefox';
        if (preg_match('/Safari/i', $userAgent)) return 'Safari';
        if (preg_match('/Edge/i', $userAgent)) return 'Edge';
        if (preg_match('/Opera/i', $userAgent)) return 'Opera';

        return 'Other';
    }

    /**
     * Parses OS from user agent
     * 
     * @param string $userAgent
     * @return string|null
     */
    private function parseOS(string $userAgent): ?string
    {
        if (empty($userAgent)) {
            return null;
        }

        if (preg_match('/Windows/i', $userAgent)) return 'Windows';
        if (preg_match('/Macintosh|Mac OS X/i', $userAgent)) return 'macOS';
        if (preg_match('/Linux/i', $userAgent)) return 'Linux';
        if (preg_match('/Android/i', $userAgent)) return 'Android';
        if (preg_match('/iOS|iPhone|iPad/i', $userAgent)) return 'iOS';

        return 'Other';
    }
}
