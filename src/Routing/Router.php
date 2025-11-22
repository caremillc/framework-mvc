<?php
namespace Careminate\Routing;

use Careminate\Http\Middlewares\Middleware;
use Careminate\Routing\Contracts\RouterInterface;
use Exception;

class Router implements RouterInterface
{
    /**
     * Registered routes by HTTP verb
     */
    protected static array $routes = [
        'GET'    => [],
        'POST'   => [],
        'PUT'    => [],
        'PATCH'  => [],
        'DELETE' => [],
    ];

    /**
     * Public folder prefix in URL (default: public)
     */
    protected static string $public = 'public';

    /**
     * Fallback handler for 404 errors
     */
    protected static $fallback = null;

/**
 * Route group stack.
 */
    protected static array $groupStack = [];

    // ---------------------------------------------------------
    //  PUBLIC PATH CONTROLLER
    // ---------------------------------------------------------
    public static function public_path(?string $path = null): string
    {
        if ($path !== null) {
            static::$public = trim($path, '/');
        }
        return static::$public;
    }

    // ---------------------------------------------------------
    //  ROUTE REGISTRATION
    // ---------------------------------------------------------
    public static function add(string $method, string $route, $controller, $action = null, array $middleware = []): void
    {
        $method = strtoupper($method);
        $route  = trim($route, '/');

        $data = [
            'controller' => $controller,
            'action'     => $action,
            'middleware' => $middleware,
        ];

        $merged = static::mergeGroupAttributes($route, $data);
// dd($merged);
        static::$routes[$method][$merged['route']] = $merged['data'];

        // static::$routes[$method][$route] = [
        //     'controller' => $controller,
        //     'action'     => $action,
        //     'middleware' => $middleware
        // ];
    }

    // ---------------------------------------------------------
    //  FALLBACK
    // ---------------------------------------------------------
    public static function fallback($callback): void
    {
        static::$fallback = $callback;
    }

    // ---------------------------------------------------------
    //  GET ROUTES (for debugging)
    // ---------------------------------------------------------
    public function routes(): array
    {
        return static::$routes;
    }

    // ---------------------------------------------------------
    //  DISPATCH
    // ---------------------------------------------------------
    public static function dispatch($uri, $method)
    {
        // Normalize HTTP method
        $method = strtoupper($method);
// dd($method);
        // Clean query string
        $path = parse_url($uri, PHP_URL_PATH);
// dd($path);
        // Remove /public if in URL
        if (static::$public !== '') {
            $path = preg_replace("#^/?" . static::$public . "#", '', $path);
        }
        // remove the leading slash
        $cleanUri = trim($path, '/');
// dd($cleanUri);
        // Skip favicon
        if ($cleanUri === 'favicon.ico') {
            return;
        }

        // No routes for this HTTP verb?
        if (! isset(static::$routes[$method])) {
            return static::handleFallback($cleanUri);
        }

        // ---------------------------------------------------------
        //  ROUTE MATCHING
        // ---------------------------------------------------------
        foreach (static::$routes[$method] as $route => $data) {

            // Transform {id} to regex
            $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[a-zA-Z0-9_-]+)', $route);
            $regex = "#^{$regex}$#";

            if (preg_match($regex, $cleanUri, $matches)) {

                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                $controller = $data['controller'];
                $action     = $data['action'];
                $middleware = $data['middleware'];

                // ---------------------------------------------------------
                // 1. Closure route
                // ---------------------------------------------------------
                if ($controller instanceof \Closure) {

                    $next = fn($req) => call_user_func_array($controller, $params);

                    $next = Middleware::handleMiddleware($middleware, $next);

                    return $next($cleanUri);
                }

                // ---------------------------------------------------------
                // 2. Controller class
                // ---------------------------------------------------------
                if (is_string($controller) && class_exists($controller)) {

                    $instance = new $controller;

                    // Invokable
                    if (is_callable($instance)) {

                        $next = fn($req) => call_user_func_array($instance, $params);

                        $next = Middleware::handleMiddleware($middleware, $next);

                        return $next($cleanUri);
                    }

                    // Controller::method
                    if ($action && method_exists($instance, $action)) {

                        $next = fn($req) =>
                        call_user_func_array([$instance, $action], $params);

                        $next = Middleware::handleMiddleware($middleware, $next);

                        return $next($cleanUri);
                    }

                    throw new Exception("Action '{$action}' not found in controller {$controller}");
                }

                throw new Exception("Invalid controller type for route '{$route}'");
            }
        }

        return static::handleFallback($cleanUri);
    }

    // ---------------------------------------------------------
    //  FALLBACK HANDLER
    // ---------------------------------------------------------
    protected static function handleFallback(string $uri)
    {
        if (! static::$fallback) {
            throw new Exception("Route '{$uri}' not found.");
        }

        $fallback = static::$fallback;

        if ($fallback instanceof \Closure  || is_callable($fallback)) {
            return $fallback();
        }

        if (is_string($fallback) && class_exists($fallback)) {
            $instance = new $fallback;

            if (is_callable($instance)) {
                return $instance();
            }

            if (method_exists($instance, 'handle')) {
                return $instance->handle();
            }

            throw new Exception("Fallback controller must be invokable or provide handle().");
        }

        if (is_array($fallback)) {
            return call_user_func($fallback);
        }

        throw new Exception("Invalid fallback configuration.");
    }

    /**
     * Push a new route group onto the stack.
     */
    public static function pushGroup(array $attributes): void
    {
        $parent = end(static::$groupStack) ?: [];

        // Merge prefix
        if (isset($parent['prefix']) && isset($attributes['prefix'])) {
            $attributes['prefix'] = trim($parent['prefix'], '/') . '/' . trim($attributes['prefix'], '/');
        } elseif (isset($parent['prefix'])) {
            $attributes['prefix'] = $parent['prefix'];
        }

        // Merge middleware
        if (isset($parent['middleware']) && isset($attributes['middleware'])) {
            $attributes['middleware'] = array_merge(
                (array) $parent['middleware'],
                (array) $attributes['middleware']
            );
        } elseif (isset($parent['middleware'])) {
            $attributes['middleware'] = (array) $parent['middleware'];
        }

        static::$groupStack[] = $attributes;
    }

/**
 * Pop last group from the stack.
 */
    public static function popGroup(): void
    {
        array_pop(static::$groupStack);
    }

/**
 * Apply active group attributes to a route definition.
 */
    public static function mergeGroupAttributes(string $route, array $data): array
    {
        if (empty(static::$groupStack)) {
            return ['route' => $route, 'data' => $data];
        }

        foreach (static::$groupStack as $group) {

            // Prefix
            if (isset($group['prefix'])) {
                $route = trim($group['prefix'], '/') . '/' . trim($route, '/');
            }

            // Middleware merge
            if (isset($group['middleware'])) {
                $data['middleware'] = array_merge(
                    (array) $group['middleware'],
                    (array) $data['middleware']
                );
            }
        }

        return ['route' => trim($route, '/'), 'data' => $data];
    }

}
