<?php

namespace Bavix\WalletVacuum;

use Bavix\WalletVacuum\Commands\WarmUpCommand;
use Bavix\WalletVacuum\Services\StoreService;
use Illuminate\Support\ServiceProvider;
use Bavix\Wallet\Interfaces\Storable;

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
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([WarmUpCommand::class]);

        $this->app->singleton(StoreService::class, StoreService::class);
        $this->app->singleton(Storable::class, Store::class);
    }

}
