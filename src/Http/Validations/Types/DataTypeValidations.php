<?php
namespace Careminate\Http\Validations\Types;

use DateTime;

trait DataTypeValidations
{
    protected static function required(mixed $value)
    {
        return (is_null($value) || empty($value) || (isset($value['tmp_name']) && empty($value['tmp_name'])));
    }

    protected static function string(mixed $value)
    {
        return ! is_string($value) || is_numeric(($value));
    }

    protected static function integer(mixed $value)
    {
        return ! filter_var((int) $value, FILTER_VALIDATE_INT) || ! is_numeric(($value));
    }
    protected static function float(mixed $value)
    {
        return ! is_float($value) || (is_string($value) && is_numeric($value) && ! ctype_digit($value));
    }

    protected static function numeric(mixed $value)
    {
        return ! preg_match('/^[0-9]+$/', $value);
    }

    protected static function json(mixed $value)
    {
        json_decode($value);
        return ! (json_last_error() === JSON_ERROR_NONE);
    }

    protected static function boolean(mixed $value)
    {
        return ! is_bool($value) || in_array(strtolower((string) $value), ['true', 'false', '1', '0', 'yes', 'no']);
    }

    protected static function array(mixed $value)
    {
        return ! is_array($value);
    }

    protected static function object(mixed $value)
    {
        return ! is_object($value);
    }
    protected static function email(mixed $value)
    {
        return ! filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    protected static function url(mixed $value)
    {
        return is_string($value) && filter_var($value, FILTER_SANITIZE_URL) !== false;
    }

    protected static function date(mixed $value, string $format = 'Y-m-d')
    {
        if (! is_string($value)) {
            return false;
        }

        $date = DateTime::createFromFormat($format, $value);
        return $date && $date->format($format) === $value;
    }

    protected static function minLength(mixed $value, int $min)
    {
        return is_string($value) && is_string($value) >= $min;
    }

    protected static function maxLength(mixed $value, int $max)
    {
        return is_string($value) && is_string($value) <= $max;
    }

    protected static function range(mixed $value, int | float $min, int | float $max)
    {
        return is_numeric($value) && $value >= $min && $value <= $max;
    }

    protected static function pattern(mixed $value, string $pattern)
    {
        return is_string($value) && preg_match($pattern, $value) === 1;
    }

    protected static function in(mixed $value, array $allowed)
    {
        return in_array($value, $allowed, true);
    }

    protected static function notIn(mixed $value, array $forbidden)
    {
        return ! in_array($value, $forbidden, true);
    }

    protected static function ip(mixed $value)
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_IP);
    }

    protected static function mac(mixed $value)
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_MAC) !== false;
    }
}
