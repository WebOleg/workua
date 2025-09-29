<?php

namespace App\Infrastructure\Cache;

use App\Domain\Link\Contracts\CacheInterface;
use App\Domain\Link\Models\Link;
use Illuminate\Support\Facades\Cache;

/**
 * RedisLinkCache
 * 
 * Redis implementation of CacheInterface
 */
class RedisLinkCache implements CacheInterface
{
    private const CACHE_PREFIX = 'link:';
    private const DEFAULT_TTL = 3600; // 1 hour

    /**
     * Caches a link by its short code
     * 
     * @param string $shortCode
     * @param Link $link
     * @param int|null $ttl Time to live in seconds
     * @return void
     */
    public function put(string $shortCode, Link $link, ?int $ttl = null): void
    {
        $key = $this->getCacheKey($shortCode);
        $ttl = $ttl ?? self::DEFAULT_TTL;

        Cache::put($key, $link, $ttl);
    }

    /**
     * Retrieves a cached link
     * 
     * @param string $shortCode
     * @return Link|null
     */
    public function get(string $shortCode): ?Link
    {
        $key = $this->getCacheKey($shortCode);
        return Cache::get($key);
    }

    /**
     * Removes a link from cache
     * 
     * @param string $shortCode
     * @return void
     */
    public function forget(string $shortCode): void
    {
        $key = $this->getCacheKey($shortCode);
        Cache::forget($key);
    }

    /**
     * Checks if link exists in cache
     * 
     * @param string $shortCode
     * @return bool
     */
    public function has(string $shortCode): bool
    {
        $key = $this->getCacheKey($shortCode);
        return Cache::has($key);
    }

    /**
     * Generates cache key with prefix
     * 
     * @param string $shortCode
     * @return string
     */
    private function getCacheKey(string $shortCode): string
    {
        return self::CACHE_PREFIX . $shortCode;
    }
}
