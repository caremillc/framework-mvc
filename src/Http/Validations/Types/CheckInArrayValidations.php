<?php
namespace Careminate\Http\Validations\Types;

trait CheckInArrayValidations
{

    protected static function in(mixed $rule, mixed $value)
    {
        $values = isset(explode(':', $rule)[1]) ? explode(',', explode(':', $rule)[1]) : [];
        return !in_array($value, $values);
    }
}


