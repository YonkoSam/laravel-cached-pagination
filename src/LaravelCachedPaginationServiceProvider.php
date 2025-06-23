<?php

namespace Yonko\LaravelCachedPagination;

use Illuminate\Database\Eloquent\Builder;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelCachedPaginationServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('laravel-cached-pagination')
            ->hasConfigFile('cached-pagination');
    }

    /**
     * The `packageBooted` method will be executed
     * after all the package's features have been booted.
     */
    public function packageBooted(): void
    {
        Builder::mixin(new CachedPaginationMacro);
    }
}
