<?php 
namespace Careminate\Http\Middlewares;

use App\Http\Kernel;
use Careminate\Logs\Log;
use Careminate\Routing\Segment;

class Middleware 
{
     public static function handleMiddleware($middlewareStack, $next)
    {
        if (! empty($middlewareStack) && is_array($middlewareStack)) {
            foreach (array_reverse($middlewareStack) as $middleware) {
                $next = function ($request) use ($middleware, $next) {
                    $role = explode(',', $middleware);
                    // var_dump($role);
                    $middleware = array_shift($role);
                    // dd($middleware);
                    // die();
                    if (! class_exists($middleware)) {
                        $middleware = self::getFromKernel($middleware);
                    }
                    return (new $middleware)->handle($request, $next, ...$role);
                };
            }
        }
        return $next;
    }


     public static function getFromKernel($key)
    {
        $type = Segment::get(0) == 'api' ? 'api' : 'web';
        // var_dump($type);
        if ($type == 'web' && isset(Kernel::$middlewareWebRoute[$key])) {
            // dd(Kernel::$middlewareWebRoute[$key]);
            return Kernel::$middlewareWebRoute[$key];
        } elseif ($type == 'api' && isset(Kernel::$middlewareApiRoute[$key])) {
            return Kernel::$middlewareApiRoute[$key];
        } else {
            throw new Log('This Middleware (' . $key . ') Not Found ');

        }
    }
}
