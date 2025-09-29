<?php

namespace App\Domain\Link\ValueObjects;

use InvalidArgumentException;

/**
 * ShortCode Value Object
 * 
 * Immutable representation of a short code
 */
final class ShortCode
{
    private const MIN_LENGTH = 6;
    private const MAX_LENGTH = 10;
    private const ALLOWED_CHARACTERS = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * Creates a new ShortCode instance
     * 
     * @param string $value The short code value
     * @throws InvalidArgumentException If code is invalid
     */
    public function __construct(private readonly string $value)
    {
        $this->validate();
    }

    /**
     * Validates the short code format
     * 
     * @throws InvalidArgumentException
     */
    private function validate(): void
    {
        $length = strlen($this->value);

        if ($length < self::MIN_LENGTH || $length > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Short code must be between %d and %d characters', self::MIN_LENGTH, self::MAX_LENGTH)
            );
        }

        if (!$this->containsOnlyAllowedCharacters()) {
            throw new InvalidArgumentException('Short code contains invalid characters');
        }
    }

    /**
     * Checks if code contains only allowed characters
     * 
     * @return bool
     */
    private function containsOnlyAllowedCharacters(): bool
    {
        $allowedChars = str_split(self::ALLOWED_CHARACTERS);
        $codeChars = str_split($this->value);

        foreach ($codeChars as $char) {
            if (!in_array($char, $allowedChars, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Gets the short code value
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
     * Generates a random short code
     * 
     * @param int $length Code length (default 7)
     * @return self
     */
    public static function generate(int $length = 7): self
    {
        $characters = self::ALLOWED_CHARACTERS;
        $charactersLength = strlen($characters);
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, $charactersLength - 1)];
        }

        return new self($code);
    }
}
