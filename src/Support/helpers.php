<?php declare (strict_types = 1);

use Careminate\Http\Resquests\Request;

// start request
if (! function_exists('base_path')) {
    function base_path(?string $file = null)
    {
        return ROOT_PATH . '/../' . $file;
    }
}

if (! function_exists('config')) {
    function config(?string $file = null)
    {
        $seprate = explode('.', $file);
        if ((! empty($seprate) && count($seprate) > 1) && ! is_null($file)) {
            $file = include base_path('config/') . $seprate[0] . '.php';
            return isset($file[$seprate[1]]) ? $file[$seprate[1]] : $file;
        }
        return $file;
    }
}

if (! function_exists('request')) {
    function request(?string $name = null, mixed $default = null)
    {
        if (empty($name)) {
            return Request::all();
        } else {
            return Request::get($name, $default);
        }
    }
}

// end request

// start routes
if (! function_exists('route_path')) {
    function route_path(?string $file = null): string
    {
        $path = config('route.path');
        return $file ? $path . '/' . $file : $path;
    }
}

// end routes

// start Hashes

if (! function_exists('bcrypt')) {
    function bcrypt(string $str)
    {
        return \Careminate\Hashes\Hash::make($str);
    }
}

if (! function_exists('hash_check')) {
    function hash_check(string $pass, string $hash)
    {
        return \Careminate\Hashes\Hash::check($pass, $hash);
    }
}

if (! function_exists('encrypt')) {
    function encrypt(string $value)
    {
        return \Careminate\Hashes\Hash::encrypt($value);
    }
}

if (! function_exists('decrypt')) {
    function decrypt(string $value)
    {
        return \Careminate\Hashes\Hash::decrypt($value);
    }
}
// end hashes

// start middlewares 
if (!function_exists('url')) {
    function url(string $url = ''): string
    {
        return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . ROOT_DIR . ltrim($url, '/');
    }
}

// end middleware