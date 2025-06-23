<?php

namespace Yonko\LaravelCachedPagination\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use Yonko\LaravelCachedPagination\LaravelCachedPaginationServiceProvider;
use Yonko\LaravelCachedPagination\Tests\TestSupport\Models\TestModel;

class CachedPaginationTest extends TestCase
{
    /**
     * Get package providers for testing.
     */
    protected function getPackageProviders($app)
    {
        return [
            LaravelCachedPaginationServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->timestamps();
        });

        for ($i = 0; $i < 20; $i++) {
            TestModel::create(['name' => 'User '.$i]);
        }
    }

    /** @test */
    public function it_caches_paginated_results()
    {
        DB::enableQueryLog();
        TestModel::cachedPaginate(60);
        $this->assertCount(2, DB::getQueryLog());
        DB::flushQueryLog();

        TestModel::cachedPaginate(60);
        $this->assertCount(0, DB::getQueryLog());
    }

    /** @test */
    public function it_clears_the_cache_when_a_model_is_updated()
    {
        TestModel::cachedPaginate(60);

        DB::enableQueryLog();
        TestModel::cachedPaginate(60);

        $this->assertCount(0, DB::getQueryLog());

        TestModel::first()->update(['name' => 'updated']);

        DB::flushQueryLog();

        TestModel::cachedPaginate(60);

        $this->assertCount(2, DB::getQueryLog());
    }

    /** @test */
    public function it_uses_the_default_ttl_from_the_config()
    {
        config(['cached-pagination.ttl' => 12345]);

        TestModel::cachedPaginate();

        $key = TestModel::query()->getCacheKey('paginate', 15, 'page', 1);
        $this->assertNotNull(Cache::get($key)->get());
    }

    /** @test */
    public function it_does_not_clear_cache_on_update_if_disabled_in_config()
    {
        config(['cached-pagination.clear_on_update' => false]);

        TestModel::cachedPaginate();
        DB::enableQueryLog();
        TestModel::cachedPaginate();
        $this->assertCount(0, DB::getQueryLog());
        TestModel::first()->update(['name' => 'updated']);
        DB::flushQueryLog();
        TestModel::cachedPaginate();
        $this->assertCount(0, DB::getQueryLog());
    }

    /** @test */
    public function it_clears_cache_on_update_if_enabled_in_config()
    {
        config(['cached-pagination.clear_on_update' => true]);

        TestModel::cachedPaginate();

        TestModel::first()->update(['name' => 'updated again']);

        DB::enableQueryLog();

        TestModel::cachedPaginate();
        $this->assertCount(2, DB::getQueryLog());
    }
}
