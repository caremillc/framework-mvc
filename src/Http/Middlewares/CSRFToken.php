<?php declare(strict_types=1);
namespace Careminate\Http\Middlewares;

use Careminate\Logs\Logger;
use Careminate\Sessions\Session;
use Careminate\Http\Requests\Request;

class CSRFToken
{
    /**
     * to handle and check the csrf token 
     */
    public function __construct()
    {

        if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' && (empty(Request::get('_token')) || Request::get('_token') !== Session::get('csrf_token'))) {
            throw new Logger('Invalid CSRF token');
        }
    }

    /**
     * to generate a new csrf token 
     * @return string
     */
    public static function generateCSRFToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
