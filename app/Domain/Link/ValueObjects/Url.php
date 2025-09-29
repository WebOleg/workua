<?php

namespace App\Domain\Link\ValueObjects;

use InvalidArgumentException;

/**
 * Url Value Object
 * 
 * Immutable representation of a validated URL
 */
final class Url
{
    private const MAX_LENGTH = 2048;

    /**
     * Creates a new Url instance
     * 
     * @param string $value The URL value
     * @throws InvalidArgumentException If URL is invalid
     */
    public function __construct(private readonly string $value)
    {
        $this->validate();
    }

    /**
     * Validates the URL format
     * 
     * @throws InvalidArgumentException
     */
    private function validate(): void
    {
        if (empty($this->value)) {
            throw new InvalidArgumentException('URL cannot be empty');
        }

        if (strlen($this->value) > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('URL cannot exceed %d characters', self::MAX_LENGTH)
            );
        }

        if (!filter_var($this->value, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid URL format');
        }

        if (!$this->hasValidScheme()) {
            throw new InvalidArgumentException('URL must use http or https scheme');
        }
    }

    /**
     * Checks if URL has valid scheme (http/https)
     * 
     * @return bool
     */
    private function hasValidScheme(): bool
    {
        return preg_match('/^https?:\/\//i', $this->value) === 1;
    }

    /**
     * Gets the URL value
     * 
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Converts to string
     * 
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Gets the domain from URL
     * 
     * @return string
     */
    public function getDomain(): string
    {
        $parsed = parse_url($this->value);
        return $parsed['host'] ?? '';
    }
}
