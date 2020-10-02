<?php

namespace Bavix\WalletVacuum;

use Bavix\Wallet\Interfaces\Storable;
use Bavix\WalletVacuum\Commands\WarmUpCommand;
use Bavix\WalletVacuum\Services\StoreService;
use Illuminate\Support\ServiceProvider;

class VacuumServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     * @codeCoverageIgnore
     */
    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([WarmUpCommand::class]);

        if (function_exists('config_path')) {
            $this->publishes([
                dirname(__DIR__).'/config/config.php' => config_path('wallet-vacuum.php'),
            ], 'laravel-wallet-vacuum-config');
        }
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/config/config.php',
            'wallet-vacuum'
        );

        $this->app->singleton(StoreService::class, StoreService::class);
        $this->app->singleton(Storable::class, Store::class);
    }
}
