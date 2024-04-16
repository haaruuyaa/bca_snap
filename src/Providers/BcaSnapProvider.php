<?php

namespace Haaruuyaa\BcaSnap\Providers;

use Illuminate\Support\ServiceProvider;

class BcaSnapProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     * @return void
     */
    public function boot(): void
    {
        $this->publishConfig();
        $this->publishMigrations();
    }

    private function publishConfig(): void
    {
        $path = $this->getConfigPath();
        $this->publishes([$path => config_path('bca.php')], 'config');
    }

    private function publishMigrations(): void
    {
        $path = $this->getMigrationsPath();
        $this->publishes([$path => database_path('migrations')], 'migrations');
    }

    private function getConfigPath(): string
    {
        return __DIR__ . '/../config/bca.php';
    }

    private function getMigrationsPath(): string
    {
        return __DIR__ . '/../database/migrations/';
    }
}
