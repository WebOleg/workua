<?php

namespace App\Domain\Link\Listeners;

use App\Domain\Link\Events\LinkVisited;
use App\Domain\Link\Models\LinkVisit;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * RecordLinkVisit Listener
 * 
 * Records visit analytics when a link is accessed
 */
class RecordLinkVisit implements ShouldQueue
{
    /**
     * Handle the event
     * 
     * @param LinkVisited $event The link visited event
     * @return void
     */
    public function handle(LinkVisited $event): void
    {
        LinkVisit::create([
            'link_id' => $event->link->id,
            'ip_address' => $event->visitData['ip'] ?? null,
            'user_agent' => $event->visitData['user_agent'] ?? null,
            'referer' => $event->visitData['referer'] ?? null,
            'visited_at' => now(),
        ]);
    }
}
