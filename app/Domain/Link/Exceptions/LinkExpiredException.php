<?php

namespace App\Domain\Link\Exceptions;

use Exception;

/**
 * LinkExpiredException
 * 
 * Thrown when attempting to access an expired link
 */
class LinkExpiredException extends Exception
{
    /**
     * Creates a new exception instance
     * 
     * @param string $shortCode The expired short code
     */
    public function __construct(string $shortCode)
    {
        parent::__construct("Link with short code '{$shortCode}' has expired", 410);
    }
}
