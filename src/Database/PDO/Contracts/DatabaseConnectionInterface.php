<?php declare(strict_types=1);
namespace Careminate\Database\PDO\Contracts;

interface DatabaseConnectionInterface
{
    public function getPDO(): \PDO;
}