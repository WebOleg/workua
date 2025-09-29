<?php

namespace App\Domain\Link\Contracts;

use App\Domain\Link\Models\Link;

/**
 * Cache Interface
 * 
 * Defines contract for caching operations
 */
interface CacheInterface
{
    /**
     * Caches a link by its short code
     * 
     * @param string $shortCode
     * @param Link $link
     * @param int|null $ttl Time to live in seconds
     * @return void
     */
    public function put(string $shortCode, Link $link, ?int $ttl = null): void;

    /**
     * Retrieves a cached link
     * 
     * @param string $shortCode
     * @return Link|null
     */
    public function get(string $shortCode): ?Link;

    /**
     * Removes a link from cache
     * 
     * @param string $shortCode
     * @return void
     */
    public function forget(string $shortCode): void;

    /**
     * Checks if link exists in cache
     * 
     * @param string $shortCode
     * @return bool
     */
    public function has(string $shortCode): bool;
}
