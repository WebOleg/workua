<?php

namespace App\Domain\Link\Services;

use App\Domain\Link\Contracts\LinkRepositoryInterface;
use App\Domain\Link\Contracts\CacheInterface;
use App\Domain\Link\Models\Link;
use App\Domain\Link\ValueObjects\ShortCode;
use App\Domain\Link\ValueObjects\Url;
use App\Domain\Link\Exceptions\InvalidUrlException;
use App\Domain\Link\Exceptions\DuplicateShortCodeException;
use Illuminate\Support\Facades\DB;

/**
 * LinkShortenerService
 * 
 * Handles the business logic for shortening URLs
 */
class LinkShortenerService
{
    private const MAX_GENERATION_ATTEMPTS = 10;

    /**
     * Create a new service instance
     * 
     * @param LinkRepositoryInterface $repository Link data repository
     * @param CacheInterface $cache Cache service
     */
    public function __construct(
        private readonly LinkRepositoryInterface $repository,
        private readonly CacheInterface $cache
    ) {}

    /**
     * Shorten a URL and create a new link
     * 
     * Uses database transaction to ensure data consistency
     * 
     * @param string $originalUrl The URL to shorten
     * @param int|null $ttlMinutes Time to live in minutes (null = no expiration)
     * @param string|null $customCode Optional custom short code
     * @return Link The created link
     * @throws InvalidUrlException If URL is invalid
     * @throws DuplicateShortCodeException If custom code already exists
     */
    public function shorten(
        string $originalUrl, 
        ?int $ttlMinutes = null, 
        ?string $customCode = null
    ): Link {
        return DB::transaction(function () use ($originalUrl, $ttlMinutes, $customCode) {
            $url = new Url($originalUrl);
            
            $shortCode = $customCode 
                ? $this->validateCustomCode($customCode)
                : $this->generateUniqueShortCode();

            $expiresAt = $ttlMinutes 
                ? now()->addMinutes($ttlMinutes)
                : null;

            $link = $this->repository->create([
                'original_url' => $url->value(),
                'short_code' => $shortCode->value(),
                'expires_at' => $expiresAt,
            ]);

            $this->cache->put($shortCode->value(), $link, $expiresAt ? $expiresAt->diffInSeconds(now()) : 3600);

            return $link;
        });
    }

    /**
     * Validate custom short code
     * 
     * @param string $code The custom code to validate
     * @return ShortCode Validated short code value object
     * @throws DuplicateShortCodeException If code already exists
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
     * Generate unique short code
     * 
     * @return ShortCode Generated unique short code
     * @throws \RuntimeException If unable to generate unique code after max attempts
     */
    private function generateUniqueShortCode(): ShortCode
    {
        for ($i = 0; $i < self::MAX_GENERATION_ATTEMPTS; $i++) {
            $shortCode = ShortCode::generate();

            if (!$this->repository->shortCodeExists($shortCode->value())) {
                return $shortCode;
            }
        }

        throw new \RuntimeException('Unable to generate unique short code after ' . self::MAX_GENERATION_ATTEMPTS . ' attempts');
    }
}
