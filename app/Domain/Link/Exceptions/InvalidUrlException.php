<?php

namespace App\Domain\Link\Exceptions;

use Exception;

/**
 * InvalidUrlException
 * 
 * Thrown when URL validation fails
 */
class InvalidUrlException extends Exception
{
    /**
     * Creates a new exception instance
     * 
     * @param string $url The invalid URL
     * @param string $reason Reason for invalidity
     */
    public function __construct(string $url, string $reason = 'Invalid URL format')
    {
        parent::__construct("{$reason}: {$url}", 422);
    }
}
