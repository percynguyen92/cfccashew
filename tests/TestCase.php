<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Track connections that have already been seeded.
     */
    protected static array $importSeededConnections = [];

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->shouldSeedImportData() && ! $this->isUsingRefreshDatabaseTrait()) {
            $this->seedImportData();
        }
    }

    protected function afterRefreshingDatabase()
    {
        parent::afterRefreshingDatabase();

        static::$importSeededConnections = [];

        if ($this->shouldSeedImportData()) {
            $this->seedImportData();
        }
    }

    protected function shouldSeedImportData(): bool
    {
        return File::exists(database_path('seeders/import_data.sql'));
    }

    protected function isUsingRefreshDatabaseTrait(): bool
    {
        $traits = class_uses_recursive(static::class);

        return in_array(RefreshDatabase::class, $traits, true)
            || in_array(DatabaseMigrations::class, $traits, true)
            || in_array(DatabaseTransactions::class, $traits, true);
    }

    protected function seedImportData(): void
    {
        $connection = DB::connection();
        $connectionName = $connection->getName();

        if (static::$importSeededConnections[$connectionName] ?? false) {
            return;
        }

        $sqlPath = database_path('seeders/import_data.sql');
        if (! File::exists($sqlPath)) {
            return;
        }

        $this->disableForeignKeyChecks($connection);

        foreach (['cutting_tests', 'containers', 'bills'] as $table) {
            $connection->table($table)->truncate();
        }

        $connection->unprepared(File::get($sqlPath));

        $this->enableForeignKeyChecks($connection);

        static::$importSeededConnections[$connectionName] = true;
    }

    protected function disableForeignKeyChecks($connection): void
    {
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $connection->statement('PRAGMA foreign_keys = OFF');
        } elseif ($driver === 'mysql') {
            $connection->statement('SET FOREIGN_KEY_CHECKS=0');
        } elseif ($driver === 'pgsql') {
            $connection->statement('SET session_replication_role = replica');
        }
    }

    protected function enableForeignKeyChecks($connection): void
    {
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $connection->statement('PRAGMA foreign_keys = ON');
        } elseif ($driver === 'mysql') {
            $connection->statement('SET FOREIGN_KEY_CHECKS=1');
        } elseif ($driver === 'pgsql') {
            $connection->statement('SET session_replication_role = default');
        }
    }
}
