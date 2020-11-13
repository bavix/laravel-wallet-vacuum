<?php

namespace Bavix\WalletVacuum\Test;

use Bavix\WalletVacuum\VacuumServiceProvider;
use Illuminate\Foundation\Application;

class TestCase extends \Bavix\Wallet\Test\TestCase
{
    /**
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            VacuumServiceProvider::class,
        ]);
    }
}
