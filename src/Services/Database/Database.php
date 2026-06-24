<?php

namespace Panelis\Database\Services\Database;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Manager;
use Panelis\Database\Services\Database\Contracts\Database as Contract;
use Panelis\Database\Services\Database\Enums\DatabaseDriver;
use Panelis\Database\Services\Database\Vendors\MySQL;
use Panelis\Database\Services\Database\Vendors\PostgreSQL;
use Panelis\Database\Services\Database\Vendors\SQLite;

/**
 * @mixin Database
 */
class Database extends Manager
{
    protected $drivers = [
        DatabaseDriver::MySQL->value,
        DatabaseDriver::PostgreSQL->value,
        DatabaseDriver::SQLite->value,
    ];

    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    public function getDefaultDriver(): string
    {
        return DatabaseDriver::SQLite->value;
    }

    protected function createSqliteDriver(): Contract
    {
        return $this->container->make(SQLite::class);
    }

    protected function createMysqlDriver(): Contract
    {
        return $this->container->make(MySQL::class);
    }

    protected function createPgsqlDriver(): Contract
    {
        return $this->container->make(PostgreSQL::class);
    }
}
