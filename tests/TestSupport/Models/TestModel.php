<?php

namespace Yonko\LaravelCachedPagination\Tests\TestSupport\Models;

use Illuminate\Database\Eloquent\Model;
use Yonko\LaravelCachedPagination\Traits\ManagesCachedPagination;

class TestModel extends Model
{
    use ManagesCachedPagination;

    protected $guarded = [];

    protected $table = 'test_models';
}
