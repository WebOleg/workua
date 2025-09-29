<?php

namespace App\Domain\Link\Events;

use App\Domain\Link\Models\Link;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * LinkVisited Event
 * 
 * Fired when a shortened link is visited by a user
 */
class LinkVisited
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance
     * 
     * @param Link $link The link that was visited
     * @param array $visitData Visit metadata (ip, user_agent, referer)
     */
    public function __construct(
        public readonly Link $link,
        public readonly array $visitData
    ) {}
}
