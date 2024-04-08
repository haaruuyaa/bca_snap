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
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }
}
