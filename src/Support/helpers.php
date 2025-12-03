<?php declare (strict_types = 1);

use Careminate\Http\Requests\Request;

// start request
if (! function_exists('base_path')) {
    function base_path(?string $file = null)
    {
        return ROOT_PATH . '/../' . $file;
    }
}

if (! function_exists('storage_path')) {
    function storage_path(?string $file = null)
    {
        return ! is_null($file) ? base_path('storage') . '/' . $file : '';
    }
}

// config() helper to load config files
if (!function_exists('config')) {
    function config(string $key, $default = null)
    {
        [$file, $k] = array_pad(explode('.', $key, 2), 2, null);
        $path = base_path("config/{$file}.php");
        if (!file_exists($path)) return $default;
        $cfg = include $path;
        if ($k === null) return $cfg;
        return $cfg[$k] ?? $default;
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
if (! function_exists('url')) {
    function url(string $url = ''): string
    {
        return $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . ROOT_DIR . ltrim($url, '/');
    }
}

// end middleware

// start view

if (! function_exists('view')) {
    /**
     * Render a view and optionally return or echo the result.
     *
     * @param string $view
     * @param array $data
     * @param bool $return If true, return the content instead of echoing
     * @return string|void
     */
    function view(string $view, array $data = [], bool $return = false)
    {
        $content = \Careminate\Views\View::make($view, $data);

        if ($return) {
            return $content;
        }

        echo $content;
    }
}

//end view

// start lang

if (! function_exists('trans')) {
    function trans(?string $trans = null, array | null $attriubtes = []): string | object
    {

        return
        ! empty($trans) ? \Careminate\Locales\Lang::get($trans, $attriubtes)
            : new \Careminate\Locales\Lang;
    }
}

// end lang

// start filesystem 

// value() helper
if (!function_exists('value')) {
    function value(mixed $value): mixed
    {
        return $value instanceof \Closure ? $value() : $value;
    }
}

// env() helper
if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null) {
            return $default;
        }

        if (is_string($value)) {
            $value = preg_replace('/\s+#.*/', '', $value);
            $value = trim($value, " \t\n\r\0\x0B\"'");
        }

        switch (strtolower((string)$value)) {
            case 'true': return true;
            case 'false': return false;
            case 'null': return null;
        }

        return $value;
    }
}

// end filesystem


// start csrf 

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return \Careminate\Sessions\Session::get('csrf_token');
    }
}

if (!function_exists('csrf')) {
    function csrf(): string
    {
        $token = \Careminate\Sessions\Session::get('csrf_token');
        return '<input type="hidden" name="_token" value="' . $token . '" />';
    }
}

// end csrf