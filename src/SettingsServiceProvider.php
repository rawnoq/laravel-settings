<?php

namespace Rawnoq\Settings;

use Illuminate\Support\ServiceProvider;
use Rawnoq\Settings\Repositories\SettingRepository;
use Rawnoq\Settings\Services\SettingService;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/settings.php',
            'settings'
        );

        $this->app->singleton(SettingRepository::class, function ($app) {
            return new SettingRepository(
                $app->make(\Rawnoq\Settings\Models\Setting::class)
            );
        });

        $this->app->singleton(SettingService::class, function ($app) {
            return new SettingService(
                $app->make(SettingRepository::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/settings.php' => config_path('settings.php'),
        ], 'settings-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'settings-migrations');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}

