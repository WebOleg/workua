<?php

namespace App\Domain\Link\Exceptions;

use Exception;

/**
 * DuplicateShortCodeException
 * 
 * Thrown when attempting to create a link with duplicate short code
 */
class DuplicateShortCodeException extends Exception
{
    /**
     * Creates a new exception instance
     * 
     * @param string $shortCode The duplicate short code
     */
    public function __construct(string $shortCode)
    {
        parent::__construct("Short code '{$shortCode}' already exists", 409);
    }
}
