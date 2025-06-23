<?php

// CachedPaginationMacro.php

namespace Yonko\LaravelCachedPagination;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;

class CachedPaginationMacro
{
    /**
     * Paginate the given query from the cache.
     *
     * @return \Closure
     */
    public function cachedPaginate()
    {
        return function ($ttl = null, $perPage = null, $columns = ['*'], $pageName = 'page', $page = null) {
            $ttl = $ttl ?? config('cached-pagination.ttl');
            $page = $page ?: Paginator::resolveCurrentPage($pageName);
            $perPage = $perPage ?: $this->model->getPerPage();

            $cacheKey = $this->getCacheKey('paginate', $perPage, $pageName, $page);

            if (! method_exists(Cache::getStore(), 'tags')) {
                return $this->paginate($perPage, $columns, $pageName, $page);
            }

            return Cache::tags($this->model::getPaginationCacheTag())->remember($cacheKey, $ttl, function () use ($perPage, $columns, $pageName, $page) {
                return $this->paginate($perPage, $columns, $pageName, $page);
            });
        };
    }

    /**
     * Paginate the given query into a simple paginator from the cache.
     *
     * @return \Closure
     */
    public function cachedSimplePaginate()
    {
        return function ($ttl = null, $perPage = null, $columns = ['*'], $pageName = 'page', $page = null) {
            $ttl = $ttl ?? config('cached-pagination.ttl');
            $page = $page ?: Paginator::resolveCurrentPage($pageName);
            $perPage = $perPage ?: $this->model->getPerPage();
            $cacheKey = $this->getCacheKey('simplePaginate', $perPage, $pageName, $page);
            if (! method_exists(Cache::getStore(), 'tags')) {
                return $this->simplePaginate($perPage, $columns, $pageName, $page);
            }

            return Cache::tags($this->model::getPaginationCacheTag())->remember($cacheKey, $ttl, function () use ($perPage, $columns, $pageName, $page) {
                return $this->simplePaginate($perPage, $columns, $pageName, $page);
            });
        };
    }

    /**
     * Paginate the given query into a cursor paginator from the cache.
     *
     * @return \Closure
     */
    public function cachedCursorPaginate()
    {
        return function ($ttl = null, $perPage = null, $columns = ['*'], $cursorName = 'cursor', $cursor = null) {
            $ttl = $ttl ?? config('cached-pagination.ttl');
            $cursor = $cursor ?: Paginator::resolveCurrentPage($cursorName);
            $perPage = $perPage ?: $this->model->getPerPage();

            $cacheKey = $this->getCacheKey('cursorPaginate', $perPage, $cursorName, $cursor);
            if (! method_exists(Cache::getStore(), 'tags')) {
                return $this->cursorPaginate($perPage, $columns, $cursorName, $cursor);
            }

            return Cache::tags($this->model::getPaginationCacheTag())->remember($cacheKey, $ttl, function () use ($perPage, $columns, $cursorName, $cursor) {
                return $this->cursorPaginate($perPage, $columns, $cursorName, $cursor);
            });
        };
    }

    /**
     * Generate a unique cache key for the query.
     *
     * @return \Closure
     */
    public function getCacheKey()
    {
        return function (...$extra) {
            $query = $this->toBase();

            $key = implode(':', [
                'cached-pagination',
                $this->model->getTable(),
                sha1($query->toSql().serialize($query->getBindings())),
                implode(':', (array) $extra),
            ]);

            return $key;
        };
    }
}
