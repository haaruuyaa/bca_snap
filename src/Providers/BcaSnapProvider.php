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
        $this->publishes([__DIR__ . '/../config/bca.php' => config_path('bca.php'),]);
    }
}
