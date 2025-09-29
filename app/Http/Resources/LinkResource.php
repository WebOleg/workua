<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * LinkResource
 * 
 * Transforms Link model for API responses
 */
class LinkResource extends JsonResource
{
    /**
     * Transform the resource into an array
     * 
     * @param Request $request The HTTP request
     * @return array<string, mixed> Transformed link data
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_url' => $this->original_url,
            'short_code' => $this->short_code,
            'short_url' => $this->getShortUrl(),
            'visits_count' => $this->getVisitsCount(),
            'unique_visitors' => $this->getUniqueVisitorsCount(),
            'is_active' => $this->isActive(),
            'is_expired' => $this->isExpired(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
