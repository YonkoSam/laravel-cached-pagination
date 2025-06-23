<?php

namespace Yonko\LaravelCachedPagination\Tests\TestSupport\Models;

use Illuminate\Database\Eloquent\Model;
use Yonko\LaravelCachedPagination\Traits\HasCachedPagination;

class TestModel extends Model
{
    use HasCachedPagination;

    protected $guarded = [];

    protected $table = 'test_models';
}
