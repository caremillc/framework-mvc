<?php 
namespace Careminate;


use App\Http\Kernel;
use Careminate\Routing\Route;
use Careminate\Routing\Segment;

/**
 * Application
 *
 * The core entry point for the Careminate framework.
 * Handles:
 *   - Bootstrapping the router
 *   - Loading route definitions
 *   - Dispatching the incoming HTTP request
 *
 * This class is typically instantiated in public/index.php
 * and represents the runtime container for a single request cycle.
 */
class Application 
{ 
    /**
     * The router instance used by the application.
     */
    protected $router;

    /**
     * Boot the application.
     *
     * This method:
     *   - Instantiates the Route facade (which also initializes the Router)
     *   - Loads route definitions from routes/web.php
     *
     * It prepares the application so the request can later be dispatched.
     */
    public function start()
    {
        // Create router instance (not strictly required since Route methods are static,
        // but instantiating makes the class feel more "service-like")
        $this->router = new Route;

       
        // Load all route definitions from routes/web.php or routes/api.php
        // `route_path()` is assumed to resolve the correct file location.
        // var_dump($_SERVER['REQUEST_URI']);
    // var_dump(Segment::get(0));
         if (Segment::get(0) == 'api') {
            $this->apiRoute();
        } else {
            $this->webRoute();
        }
    } 


    
    /**
     * Dispatch the incoming HTTP request.
     *
     * This method:
     *   - Reads the current URI and HTTP method from PHP globals
     *   - Passes the request to the Route dispatcher
     *   - The Router matches, resolves parameters, runs middleware, and calls the controller action
     */
    public function dispatch(): void
    {
        $uri    = $_SERVER['REQUEST_URI']  ?? '/';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Forward the request to the static dispatcher
        Route::dispatch($uri, $method);
    }

     public function webRoute()
    {
        foreach (Kernel::$globalWeb as $global) {
            new $global();
        }
        include route_path('web.php');
    }
    
    public function apiRoute()
    {
        foreach (Kernel::$globalApi as $global) {
            new $global();
        }
        include route_path('api.php');
    }
}
