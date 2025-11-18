<?php declare(strict_types=1);
namespace Careminate\Http\Resquests;

class Request extends FileRequest
{
     public static function get(string $name, mixed $default = null): string
    {
        return isset($_REQUEST[$name]) && !empty($_REQUEST[$name]) ? $_REQUEST[$name] : $default ?? '';
    }

    public static function all()
    {
        return count($_REQUEST) > 0 ? $_REQUEST : new self;
    }

}