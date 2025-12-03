<?php 
namespace Careminate\Http\Validations;

use Careminate\Logs\Logger;
use Careminate\Http\Validations\Types\QueryValidations;
use Careminate\Http\Validations\Types\DataTypeValidations;
use Careminate\Http\Validations\Types\CheckInArrayValidations;

class Validate
{
    use DataTypeValidations, CheckInArrayValidations, QueryValidations;
    protected static $errors = [];
    protected static $validated = [];

    /**
     * @param mixed $key
     * @param mixed $requests
     *
     * @return mixed
     */
    public static function request($key, $requests): mixed
    {
        return isset($requests[$key]) ? $requests[$key] : '';
    }

    public static function make(array|object $requests, array $rules, array|null $attributes = [])
    {
        foreach ($rules as $rule_key => $rule_value) {
            $value = self::request($rule_key, $requests);
            $real_rule_key = explode('.', $rule_key)[0];
            $attribute = self::attribute($attributes, $real_rule_key);

            foreach (array_values(self::rule($rule_value)) as $rule) {
                $method = self::getMethodName($rule);
                
                if (!method_exists(new self, $method)) {
                    throw new Logger('There is not validation called ' . $method);
                } elseif (preg_match('/^in:|^unique:|^exists:/i', $rule)) {
                    if (self::$method($rule, $value)) {
                        if (preg_match('/^in:/i', $rule)) {
                            $attribut_in = explode(':', $rule);
                            $attribute = $attribute . ' - ' . $attribut_in[1];
                        } else {
                            $attribute = $attribute;
                        }
                        self::add_error($rule_key, $method, $attribute);
                    }

                } elseif (preg_match('/\./i', $rule_key)) {
                    self::validate_sub_value($rule_key, $requests, $attribute, $rule);
                } elseif (self::$method($value)) {
                    self::add_error($rule_key, $rule, $attribute);
                } else {
                    self::$validated[$rule_key] = $value;
                }
            }
        }
        return new self;
    }

    /**
     * returend validated values
     * @return array
     */
    public static function validated()
    {
        return static::$validated;
    }

    /**
     * to return errors as array
     * @return array
     */
    public static function failed()
    {
        return static::$errors;
    }

    /**
     * @param mixed $rule
     *
     * @return string
     */
    protected static function getMethodName($rule): string
    {
        if (preg_match('/^in:/i', $rule)) {
            return 'in';
        } elseif (preg_match('/^unique:/i', $rule)) {
            return 'unique';
        } elseif (preg_match('/^exists:/i', $rule)) {
            return 'exists';
        } else {
            return $rule;
        }
    }

    /**
     * @param mixed $rule_key
     * @param mixed $requests
     * @param mixed $attribute
     * @param mixed $rule
     *
     * @return void
     */
    protected static function validate_sub_value($rule_key, $requests, $attribute, $rule)
    {
        $split_key = explode('.', $rule_key);
        if (isset($split_key[1]) && $split_key[1] == '*' && is_array($requests[$split_key[0]])) {
            $index = 0;
            foreach ($requests[$split_key[0]] as $array_value) {
                if (self::$rule($array_value)) {
                    self::add_error($split_key[0], $rule, $attribute . ' (' . $index . ') ');
                }
                $index++;
            }
        } elseif (is_array($requests[$split_key[0]]) && isset($requests[$split_key[0]]) && isset($split_key[1])) {
            $select_request = $requests[$split_key[0]];
            if (isset($select_request[$split_key[1]])) {
                if (self::$rule($select_request[$split_key[1]])) {
                    self::add_error($split_key[0], $rule, $attribute . ' (' . $split_key[1] . ') ');
                }
            }
        }
    }

    /**
     * @param mixed $key
     * @param mixed $rule
     * @param mixed $attribute
     *
     * @return void
     */
    private static function add_error($key, $rule, $attribute): void
    {
        static::$errors[$key][] = trans('validation.' . $rule, ['attribute' => $attribute]);
    }


    /**
     * @param string|array $rule
     * @return array
     */
    private static function rule(string|array $rule): array
    {
        return is_array($rule) ? $rule : explode('|', $rule);
    }

    /**
     * @param mixed $attributes
     * @param mixed $key
     *
     * @return string
     */
    private static function attribute($attributes, $key): string
    {
        return isset($attributes[$key]) ? $attributes[$key] : $key;
    }
}
