<?php declare(strict_types=1);
namespace Careminate\Database\PDO\Drivers;

use PDO;
use Careminate\Logs\Log;
use Careminate\Logs\Logger;
use Careminate\Database\PDO\Contracts\DatabaseConnectionInterface;

class SQLiteConnection implements DatabaseConnectionInterface
{
    private PDO $pdo;
    protected $path;

    public function __construct()
    {
        $config = config('database.drivers');
        $this->path = $config['sqlite']['path'];
        $dsn = "sqlite:" . $this->path;
        try {
            $this->pdo = new PDO($dsn);
            $this->pdo->setAttribute($config['sqlite']['ERRMODE'], $config['sqlite']['EXCEPTION']);
        } catch (\Exception $e) {
            throw new Logger($e->getMessage());
        }
    }


    public function getPDO(): PDO
    {
        return $this->pdo;
    }
}

