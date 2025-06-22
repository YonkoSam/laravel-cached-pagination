<?php

namespace Yonko\LaravelCachedPagination;

use Illuminate\Database\Eloquent\Builder;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelCachedPaginationServiceProvider extends PackageServiceProvider
{
    public function boot()
    {
        parent::boot();
        Builder::mixin(new CachedPaginationMacro);
    }

    public function configurePackage(Package $package): void
    {
        $package->name('laravel-cached-pagination')
            ->hasConfigFile('cached-pagination');
    }
}
