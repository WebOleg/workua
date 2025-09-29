<?php

namespace App\Domain\Link\Services;

use App\Domain\Link\Contracts\LinkRepositoryInterface;
use App\Domain\Link\Contracts\CacheInterface;
use App\Domain\Link\Models\Link;
use App\Domain\Link\ValueObjects\ShortCode;
use App\Domain\Link\ValueObjects\Url;
use App\Domain\Link\Exceptions\InvalidUrlException;
use App\Domain\Link\Exceptions\DuplicateShortCodeException;

/**
 * LinkShortenerService
 * 
 * Handles the business logic for shortening URLs
 */
class LinkShortenerService
{
    private const MAX_GENERATION_ATTEMPTS = 10;

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
     * Shortens a URL and creates a new link
     * 
     * @param string $originalUrl The URL to shorten
     * @param int|null $ttlMinutes Time to live in minutes (null = no expiration)
     * @param string|null $customCode Optional custom short code
     * @return Link The created link
     * @throws InvalidUrlException
     * @throws DuplicateShortCodeException
     */
    public function shorten(
        string $originalUrl, 
        ?int $ttlMinutes = null, 
        ?string $customCode = null
    ): Link {
        // Validate URL
        $url = new Url($originalUrl);
        
        // Generate or validate short code
        $shortCode = $customCode 
            ? $this->validateCustomCode($customCode)
            : $this->generateUniqueShortCode();

        // Calculate expiration time
        $expiresAt = $ttlMinutes ? now()->addMinutes($ttlMinutes) : null;

        // Create the link
        $link = $this->repository->create([
            'original_url' => $url->value(),
            'short_code' => $shortCode->value(),
            'expires_at' => $expiresAt,
        ]);

        // Cache the link
        $this->cacheLink($link);

        return $link;
    }

    /**
     * Validates a custom short code
     * 
     * @param string $code The custom code to validate
     * @return ShortCode Validated short code
     * @throws DuplicateShortCodeException
     */
    private function validateCustomCode(string $code): ShortCode
    {
        $shortCode = new ShortCode($code);

        if ($this->repository->shortCodeExists($shortCode->value())) {
            throw new DuplicateShortCodeException($shortCode->value());
        }

        return $shortCode;
    }

    /**
     * Generates a unique short code
     * 
     * @return ShortCode Unique short code
     * @throws \RuntimeException If unable to generate unique code
     */
    private function generateUniqueShortCode(): ShortCode
    {
        $attempts = 0;

        do {
            $shortCode = ShortCode::generate();
            $attempts++;

            if ($attempts >= self::MAX_GENERATION_ATTEMPTS) {
                throw new \RuntimeException('Unable to generate unique short code');
            }
        } while ($this->repository->shortCodeExists($shortCode->value()));

        return $shortCode;
    }

    /**
     * Caches a link for quick retrieval
     * 
     * @param Link $link The link to cache
     * @return void
     */
    private function cacheLink(Link $link): void
    {
        $ttl = $link->expires_at 
            ? $link->expires_at->diffInSeconds(now())
            : 86400; // 24 hours default

        $this->cache->put($link->short_code, $link, $ttl);
    }
}
