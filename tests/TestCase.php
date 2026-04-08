<?php

namespace Tests;

require_once __DIR__.'/helpers.php';

use Multicaret\Acquaintances\AcquaintancesServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            AcquaintancesServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'swan',
            'username' => 'root',
            'password' => 'secret',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ]);

        $app['config']->set('acquaintances.migrations', false);
        $app['config']->set('acquaintances.model_namespace', 'App');
        $app['config']->set('acquaintances.models.user', 'User');
        $app['config']->set('acquaintances.tables.interactions_user_id_fk_column_type', 'unsignedBigInteger');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/migrations');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
