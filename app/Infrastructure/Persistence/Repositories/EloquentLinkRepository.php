<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Link\Contracts\LinkRepositoryInterface;
use App\Domain\Link\Models\Link;
use Illuminate\Support\Collection;

/**
 * EloquentLinkRepository
 * 
 * Eloquent implementation of LinkRepositoryInterface
 */
class EloquentLinkRepository implements LinkRepositoryInterface
{
    /**
     * Finds a link by its short code
     * 
     * @param string $shortCode
     * @return Link|null
     */
    public function findByShortCode(string $shortCode): ?Link
    {
        return Link::where('short_code', $shortCode)->first();
    }

    /**
     * Finds an active link by short code
     * 
     * @param string $shortCode
     * @return Link|null
     */
    public function findActiveByShortCode(string $shortCode): ?Link
    {
        return Link::active()
            ->where('short_code', $shortCode)
            ->first();
    }

    /**
     * Creates a new link
     * 
     * @param array $data
     * @return Link
     */
    public function create(array $data): Link
    {
        return Link::create($data);
    }

    /**
     * Updates an existing link
     * 
     * @param Link $link
     * @param array $data
     * @return Link
     */
    public function update(Link $link, array $data): Link
    {
        $link->update($data);
        return $link->fresh();
    }

    /**
     * Deletes a link (soft delete)
     * 
     * @param Link $link
     * @return bool
     */
    public function delete(Link $link): bool
    {
        return $link->delete();
    }

    /**
     * Checks if short code exists
     * 
     * @param string $shortCode
     * @return bool
     */
    public function shortCodeExists(string $shortCode): bool
    {
        return Link::where('short_code', $shortCode)->exists();
    }

    /**
     * Gets all expired links
     * 
     * @return Collection
     */
    public function getExpiredLinks(): Collection
    {
        return Link::expired()->get();
    }
}
