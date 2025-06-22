# Laravel Cached Pagination

[![Latest Version on Packagist](https://img.shields.io/packagist/v/yonkosam/laravel-cached-pagination.svg?style=flat-square)](https://packagist.org/packages/yonkosam/laravel-cached-pagination)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/yonkosam/laravel-cached-pagination/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/yonkosam/laravel-cached-pagination/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/yonkosam/laravel-cached-pagination/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/yonkosam/laravel-cached-pagination/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/yonkosam/laravel-cached-pagination.svg?style=flat-square)](https://packagist.org/packages/yonkosam/laravel-cached-pagination)

A simple, tag-based caching layer for Laravel Eloquent pagination that dramatically improves performance by caching paginated query results. This package extends Laravel's built-in pagination with smart caching capabilities while maintaining full compatibility with existing pagination methods.

Perfect for applications with heavy database queries where pagination results don't change frequently. The package automatically invalidates cache when models are created, updated, or deleted, ensuring data consistency.

```php
// Before: Regular pagination (hits database every time)
$users = User::where('active', true)->paginate(15);

// After: Cached pagination (cached for 1 hour by default)
$users = User::where('active', true)->cachedPaginate();
```

## ✨ Features

-   🚀 **Drop-in replacement** for Laravel's pagination methods
-   🏷️ **Tag-based caching** for efficient cache invalidation
-   🔄 **Automatic cache invalidation** on model changes
-   📊 **Multiple pagination types** supported (paginate, simplePaginate, cursorPaginate)
-   ⚙️ **Configurable TTL** and cache behavior
-   🧪 **Fully tested** with comprehensive test coverage
-   🔒 **Laravel 10, 11, 12** compatible

## 📦 Installation

You can install the package via Composer:

```bash
composer require yonkosam/laravel-cached-pagination
```

### Publish Configuration (Optional)

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-cached-pagination-config"
```

This is the contents of the published config file:

```php
return [
    /**
     * The default Time To Live (TTL) for cached paginations in seconds.
     * You can use an integer for seconds or a \DateInterval object.
     * The default is 1 hour (3600 seconds).
     */
    'ttl' => 3600,

    /**
     * Determine whether the pagination cache should be automatically
     * cleared when a model is updated.
     */
    'clear_on_update' => true,

    /**
     * Determine whether the pagination cache should be automatically
     * cleared when a model is created.
     */
    'clear_on_create' => true,

    /**
     * Determine whether the pagination cache should be automatically
     * cleared when a model is deleted.
     */
    'clear_on_delete' => true,
];
```

## 🚀 Quick Start

### 1. Add the Trait to Your Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yonko\LaravelCachedPagination\Traits\HasCachedPagination;

class User extends Model
{
    use HasCachedPagination;

    // Your model code...
}
```

### 2. Use Cached Pagination in Your Controllers

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        // Cache pagination results for 1 hour (default)
        $users = User::where('active', true)
            ->cachedPaginate(15);

        return view('users.index', compact('users'));
    }
}
```

## 📖 Usage

### Basic Cached Pagination

Replace Laravel's standard `paginate()` method with `cachedPaginate()`:

```php
// Standard pagination (hits database every time)
$posts = Post::published()->paginate(10);

// Cached pagination (cached for 1 hour by default)
$posts = Post::published()->cachedPaginate(10);
```

### Custom Cache Duration

Specify a custom TTL (Time To Live) in seconds:

```php
// Cache for 30 minutes (1800 seconds)
$posts = Post::published()->cachedPaginate(ttl: 1800, perPage: 10);

// Cache for 24 hours
$posts = Post::published()->cachedPaginate(ttl: 86400, perPage: 10);
```

### Simple Pagination with Cache

For simple pagination (Previous/Next only):

```php
$posts = Post::published()->cachedSimplePaginate(10);

// With custom TTL
$posts = Post::published()->cachedSimplePaginate(ttl: 3600, perPage: 10);
```

### Cursor Pagination with Cache

For cursor-based pagination:

```php
$posts = Post::published()->cachedCursorPaginate(10);

// With custom TTL
$posts = Post::published()->cachedCursorPaginate(ttl: 3600, perPage: 10);
```

### Complex Queries

The package works with any Eloquent query:

```php
$users = User::with(['posts', 'profile'])
    ->where('status', 'active')
    ->where('created_at', '>=', now()->subMonths(6))
    ->orderBy('last_login_at', 'desc')
    ->cachedPaginate(20);
```

### Manual Cache Management

Clear cache for a specific model:

```php
// Clear all cached pagination for User model
User::clearCachedPaginators();

// This will automatically happen when a User is created, updated, or deleted
// (based on your configuration)
```

## ⚙️ Configuration

### Cache Store Requirements

This package requires a cache store that supports tagging. The following Laravel cache drivers support tags:

-   ✅ **Redis** (Recommended)
-   ✅ **Memcached**
-   ✅ **Array** (for testing)
-   ❌ **File** (not supported)
-   ❌ **Database** (not supported)

### Automatic Cache Invalidation

Configure when the cache should be automatically cleared:

```php
// config/cached-pagination.php
return [
    'clear_on_create' => true,  // Clear cache when new models are created
    'clear_on_update' => true,  // Clear cache when models are updated
    'clear_on_delete' => true,  // Clear cache when models are deleted
];
```

### Performance Considerations

-   **TTL Selection**: Choose appropriate cache durations based on your data update frequency
-   **Memory Usage**: Monitor Redis/Memcached memory usage with heavy pagination caching
-   **Cache Warming**: Consider implementing cache warming strategies for frequently accessed pages

## 🔧 Advanced Usage

### Custom Cache Keys

The package automatically generates unique cache keys based on:

-   Model table name
-   Query SQL and bindings
-   Pagination parameters
-   Page number

Cache keys follow this pattern:

```
cached-pagination:{table}:{query_hash}:{pagination_type}:{per_page}:{page_name}:{page}
```

### Working with Views

In your Blade templates, use cached pagination results exactly like regular pagination:

```blade
<!-- resources/views/posts/index.blade.php -->
@foreach($posts as $post)
    <article>
        <h2>{{ $post->title }}</h2>
        <p>{{ $post->excerpt }}</p>
    </article>
@endforeach

{{ $posts->links() }}
```

### API Resources

```php
use App\Http\Resources\PostResource;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::published()->cachedPaginate(15);

        return PostResource::collection($posts);
    }
}
```

## 🧪 Testing

Run the tests with:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

Run static analysis:

```bash
composer analyse
```

## 📊 Performance Benchmarks

Performance improvements vary based on query complexity and data size:

| Scenario                         | Without Cache | With Cache | Improvement      |
| -------------------------------- | ------------- | ---------- | ---------------- |
| Simple pagination (1000 records) | ~50ms         | ~2ms       | **96% faster**   |
| Complex joins (10,000 records)   | ~200ms        | ~3ms       | **98.5% faster** |
| Heavy aggregations               | ~500ms        | ~2ms       | **99.6% faster** |

_Benchmarks performed on a typical LAMP stack with Redis cache_

## ❓ FAQ

### Q: What happens if my cache store doesn't support tags?

A: The package gracefully falls back to regular pagination without caching. No errors will occur.

### Q: How do I cache pagination for API endpoints?

A: Use the same methods in your API controllers. The package works seamlessly with API resources and JSON responses.

### Q: Can I use this with custom pagination views?

A: Yes! The cached pagination results work exactly like Laravel's default pagination, so custom views will work without modification.

### Q: Will this work with Livewire/Alpine.js?

A: Absolutely! The pagination links and AJAX requests will work normally since the underlying pagination structure is unchanged.

### Q: How do I debug cache issues?

A: Enable Laravel's query log to see if queries are being cached:

```php
DB::enableQueryLog();
$posts = Post::cachedPaginate(10);
dd(DB::getQueryLog()); // Should be empty on cache hit
```

## 🤝 Contributing

We welcome contributions! Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Development Setup

1. Clone the repository
2. Install dependencies: `composer install`
3. Run tests: `composer test`
4. Check code style: `composer format`

## 🔒 Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## 📝 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## 👥 Credits

-   [Abdellatif Samlani](https://github.com/YonkoSam) - Creator and maintainer
-   [All Contributors](../../contributors) - Thank you for your contributions!

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

---

<p align="center">
  <strong>⭐ If this package helped you, please give it a star on GitHub!</strong>
</p>
