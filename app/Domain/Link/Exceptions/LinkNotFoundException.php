<?php

namespace App\Domain\Link\Exceptions;

use Exception;

/**
 * LinkNotFoundException
 * 
 * Thrown when a requested link is not found
 */
class LinkNotFoundException extends Exception
{
    /**
     * Creates a new exception instance
     * 
     * @param string $shortCode The short code that was not found
     */
    public function __construct(string $shortCode)
    {
        parent::__construct("Link with short code '{$shortCode}' not found", 404);
    }
}
