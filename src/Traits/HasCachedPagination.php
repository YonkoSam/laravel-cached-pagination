<?php

namespace Yonko\LaravelCachedPagination\Traits;

use Illuminate\Support\Facades\Cache;

// RENAME THE TRAIT HERE
trait HasCachedPagination
{
    /**
     * Boot the trait to register model event listeners.
     */
    protected static function bootHasCachedPagination(): void
    {
        if (! method_exists(Cache::getStore(), 'tags')) {
            return;
        }

        static::saved(function ($model) {
            if ($model->wasRecentlyCreated && config('cached-pagination.clear_on_create')) {
                static::clearCachedPaginators();
            } elseif (! $model->wasRecentlyCreated && config('cached-pagination.clear_on_update')) {
                static::clearCachedPaginators();
            }
        });

        if (config('cached-pagination.clear_on_delete')) {
            static::deleted(function () {
                static::clearCachedPaginators();
            });
        }
    }

    /**
     * Get the cache tag name for pagination related to this model.
     */
    public static function getPaginationCacheTag(): string
    {
        return 'cached-pagination:'.(new static)->getTable();
    }

    /**
     * Clear all cached paginators for this model.
     */
    public static function clearCachedPaginators(): bool
    {
        if (! method_exists(Cache::getStore(), 'tags')) {
            return false;
        }

        return Cache::tags(static::getPaginationCacheTag())->flush();
    }
}
