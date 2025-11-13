<?php

namespace Rawnoq\Settings\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Orchestra\Testbench\Concerns\WithLaravelMigrations;
use Rawnoq\Settings\SettingsServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    use WithLaravelMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [SettingsServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.locale', 'en');
        $app['config']->set('app.fallback_locale', 'en');
        $app['config']->set('translatable.locales', ['en', 'ar', 'fr']);
        $app['config']->set('translatable.locale_separator', '-');

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
    }
}
