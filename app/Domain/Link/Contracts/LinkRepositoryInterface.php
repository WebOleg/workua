<?php

namespace App\Domain\Link\Contracts;

use App\Domain\Link\Models\Link;

/**
 * LinkRepository Interface
 * 
 * Defines contract for link data persistence operations
 */
interface LinkRepositoryInterface
{
    /**
     * Finds a link by its short code
     * 
     * @param string $shortCode
     * @return Link|null Returns link if found, null otherwise
     */
    public function findByShortCode(string $shortCode): ?Link;

    /**
     * Finds an active link by short code
     * 
     * @param string $shortCode
     * @return Link|null Returns active link if found, null otherwise
     */
    public function findActiveByShortCode(string $shortCode): ?Link;

    /**
     * Creates a new link
     * 
     * @param array $data Link data
     * @return Link Created link instance
     */
    public function create(array $data): Link;

    /**
     * Updates an existing link
     * 
     * @param Link $link Link to update
     * @param array $data Update data
     * @return Link Updated link instance
     */
    public function update(Link $link, array $data): Link;

    /**
     * Deletes a link
     * 
     * @param Link $link Link to delete
     * @return bool True if deleted successfully
     */
    public function delete(Link $link): bool;

    /**
     * Checks if short code exists
     * 
     * @param string $shortCode
     * @return bool True if exists
     */
    public function shortCodeExists(string $shortCode): bool;

    /**
     * Gets all expired links
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getExpiredLinks();
}
