<?php

namespace Bavix\WalletVacuum\Commands;

use Bavix\Wallet\Models\Wallet;
use Illuminate\Console\Command;

class WarmUpCommand extends Command
{

    /**
     * @var string
     */
    protected $name = 'wallet:warm-up';

    /**
     * @var string
     */
    protected $description = 'Needed to quickly warm up the cache';

    /**
     * @return void
     */
    public function handle(): void
    {
        Wallet::query()->each(static function (Wallet $wallet) {
            return $wallet->refreshBalance();
        });
    }

}
