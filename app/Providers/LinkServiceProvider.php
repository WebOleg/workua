<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Link\Contracts\LinkRepositoryInterface;
use App\Domain\Link\Contracts\CacheInterface;
use App\Infrastructure\Persistence\Repositories\EloquentLinkRepository;
use App\Infrastructure\Cache\RedisLinkCache;

/**
 * LinkServiceProvider
 * 
 * Registers bindings for dependency injection
 */
class LinkServiceProvider extends ServiceProvider
{
    /**
     * Register services
     * 
     * @return void
     */
    public function register(): void
    {
        // Bind Repository Interface to Implementation
        $this->app->bind(
            LinkRepositoryInterface::class,
            EloquentLinkRepository::class
        );

        // Bind Cache Interface to Implementation
        $this->app->bind(
            CacheInterface::class,
            RedisLinkCache::class
        );
    }

    /**
     * Bootstrap services
     * 
     * @return void
     */
    public function boot(): void
    {
        //
    }
}
