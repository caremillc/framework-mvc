<?php 
namespace Careminate\Http\Middlewares\Contracts;

interface MiddlewareInterface 
{
    public function handle($request, $next,...$role);
    
}
