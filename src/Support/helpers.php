<?php declare(strict_types=1);

if (!function_exists('base_path')) {
    function base_path(?string $file = null)
    {
        return  ROOT_PATH . '/../' . $file;
    }
}

if (!function_exists('config')) {
    function config(?string $file = null)
    {
        $seprate = explode('.', $file);
        if ((!empty($seprate) && count($seprate) > 1) && !is_null($file)) {
            $file = include base_path('config/') . $seprate[0] . '.php';
            return isset($file[$seprate[1]]) ? $file[$seprate[1]] : $file;
        }
        return $file;
    }
}


if (!function_exists('request')) {
    function request(?string $name = null, mixed $default = null)
    {
        if (empty($name)) {
            return Request::all();
        } else {
            return Request::get($name, $default);
        }
    }
}