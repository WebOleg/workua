<?php

namespace App\Domain\Link\Services;

use App\Domain\Link\Contracts\LinkRepositoryInterface;
use App\Domain\Link\Contracts\CacheInterface;
use App\Domain\Link\Models\Link;
use App\Domain\Link\Exceptions\LinkNotFoundException;
use App\Domain\Link\Exceptions\LinkExpiredException;

/**
 * LinkResolverService
 * 
 * Handles the business logic for resolving short codes to original URLs
 */
class LinkResolverService
{
    /**
     * Creates a new service instance
     * 
     * @param LinkRepositoryInterface $repository
     * @param CacheInterface $cache
     */
    public function __construct(
        private readonly LinkRepositoryInterface $repository,
        private readonly CacheInterface $cache
    ) {}

    /**
     * Resolves a short code to its original URL
     * 
     * @param string $shortCode The short code to resolve
     * @return string The original URL
     * @throws LinkNotFoundException
     * @throws LinkExpiredException
     */
    public function resolve(string $shortCode): string
    {
        $link = $this->findLink($shortCode);

        if (!$link) {
            throw new LinkNotFoundException($shortCode);
        }

        if ($link->isExpired()) {
            throw new LinkExpiredException($shortCode);
        }

        return $link->original_url;
    }

    /**
     * Gets link details including metadata
     * 
     * @param string $shortCode
     * @return Link
     * @throws LinkNotFoundException
     * @throws LinkExpiredException
     */
    public function getLinkDetails(string $shortCode): Link
    {
        $link = $this->findLink($shortCode);

        if (!$link) {
            throw new LinkNotFoundException($shortCode);
        }

        if ($link->isExpired()) {
            throw new LinkExpiredException($shortCode);
        }

        return $link;
    }

    /**
     * Finds a link by short code (checks cache first)
     * 
     * @param string $shortCode
     * @return Link|null
     */
    private function findLink(string $shortCode): ?Link
    {
        // Try cache first for performance
        if ($this->cache->has($shortCode)) {
            return $this->cache->get($shortCode);
        }

        // Fall back to database
        $link = $this->repository->findActiveByShortCode($shortCode);

        // Cache if found
        if ($link) {
            $this->cacheLink($link);
        }

        return $link;
    }

    /**
     * Caches a link for quick retrieval
     * 
     * @param Link $link
     * @return void
     */
    private function cacheLink(Link $link): void
    {
        $ttl = $link->expires_at 
            ? $link->expires_at->diffInSeconds(now())
            : 3600; // 1 hour default

        $this->cache->put($link->short_code, $link, $ttl);
    }
}
