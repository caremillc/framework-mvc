<?php 
namespace Careminate\Routing\Contracts;

/**
 * RouterInterface
 *
 * Defines the core contract for any router implementation
 * within the Careminate framework. A class implementing
 * this interface must be able to:
 *  - Register routes
 *  - Return the list of all registered routes
 *  - Dispatch an incoming HTTP request to the correct handler
 */
interface RouterInterface
{
    /**
     * Register a route definition.
     *
     * @param string $method       The HTTP method (GET, POST, PUT, etc.)
     * @param string $route        The URI pattern to match.
     * @param mixed  $controller   Controller class name or callback handler.
     * @param mixed  $action       Method name (if using controller class).
     * @param array  $middleware   Array of middleware to apply to this route.
     *
     * @return mixed
     */
    public static function add(string $method, string $route, $controller, $action, array $middleware = []);

    /**
     * Retrieve all registered routes.
     *
     * Typically used for debugging,
     * route caching, or CLI commands such as:
     *   php caremi route:list
     *
     * @return array
     */
    public function routes();

    /**
     * Dispatch the incoming request to the appropriate route handler.
     *
     * @param string $uri      The request path (e.g. "/users/5/edit").
     * @param string $method   The HTTP method used by the request.
     *
     * The implementing class should:
     *   - Match the route
     *   - Run assigned middleware
     *   - Call the controller method or callback
     *
     * @return mixed
     */
    public static function dispatch($uri, $method);
}

