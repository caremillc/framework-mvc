<?php
namespace Careminate\Routing\Traits;

use Careminate\Routing\Router;

trait Methods
{
    // simple add wrappers
    public static function get(string $route, $controller, $action = null, array $middleware = []): void
    {
        static::add('GET', $route, $controller, $action, $middleware);
    }

    public static function post(string $route, $controller, $action = null, array $middleware = []): void
    {
        static::add('POST', $route, $controller, $action, $middleware);
    }

    public static function put(string $route, $controller, $action = null, array $middleware = []): void
    {
        static::add('PUT', $route, $controller, $action, $middleware);
    }

    public static function patch(string $route, $controller, $action = null, array $middleware = []): void
    {
        static::add('PATCH', $route, $controller, $action, $middleware);
    }

    public static function delete(string $route, $controller, $action = null, array $middleware = []): void
    {
        static::add('DELETE', $route, $controller, $action, $middleware);
    }

    public static function any(string $route, $controller, $action = null, array $middleware = []): void
    {
        static::add('GET', $route, $controller, $action, $middleware);
        static::add('POST', $route, $controller, $action, $middleware);
        static::add('PUT', $route, $controller, $action, $middleware);
        static::add('PATCH', $route, $controller, $action, $middleware);
        static::add('DELETE', $route, $controller, $action, $middleware);
    }

    public static function match(array $methods, string $route, $controller, $action = null, array $middleware = []): void
    {
        foreach ($methods as $m) {
            static::add(strtoupper($m), $route, $controller, $action, $middleware);
        }
    }

    /**
     * Register a fallback route (must match Router::fallback signature)
     * Accepts closure, callable, class name or [Class, method]
     */
    public static function fallback($callback): void
    {
        parent::fallback($callback);
    }

    /**
     * Group helper (prefix / middleware)
     * Usage:
     *  Route::group(['prefix'=>'admin','middleware'=>['auth']], function() {
     *      Route::get('dashboard', ...);
     *  });
     */
    public static function group(array $attributes, callable $callback): void
    {
        // store current group state on router
        $prefix = $attributes['prefix'] ?? '';
        $middleware = $attributes['middleware'] ?? [];

        Router::pushGroup([
            'prefix'     => trim($prefix, '/'),
            'middleware' => (array) $middleware,
        ]);

        try {
            $callback();
        } finally {
            Router::popGroup();
        }
    }
}
