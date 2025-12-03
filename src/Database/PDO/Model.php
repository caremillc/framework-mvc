<?php declare(strict_types=1);
namespace Careminate\Database\PDO;

use Careminate\Logs\Log;
use Careminate\Logs\Logger;
use Careminate\Database\PDO\BaseModel;
use Careminate\Database\PDO\Drivers\MySQLConnection;
use Careminate\Database\PDO\Drivers\SQLiteConnection;
use Careminate\Database\PDO\QueryBuilder\DBSelectors;
use Careminate\Database\PDO\QueryBuilder\DBConditions;


class Model extends BaseModel
{
    use DBConditions, DBSelectors;
    public function __construct()
    {
        $config = config('database.driver');
        if ($config == 'mysql') {
            parent::__construct(new MySQLConnection());
        } elseif ($config == 'sqlite') {
            parent::__construct(new SQLiteConnection());
        } else {
            throw new Logger('Database driver not supported');
        }
    }

    public static function getTable()
    {
        $class = new static;
        if ($class->table == null) {
            $class->table = strtolower((new \ReflectionClass(static::class))->getShortName()) . 's';
        }
        return $class->table;
    }

    public function toArray()
    {
        return (array) static::$attributes;
    }
}
