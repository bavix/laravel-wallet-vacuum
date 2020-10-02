<?php

namespace Bavix\WalletVacuum\Services;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Services\WalletService;

class StoreService
{
    /**
     * @param Wallet $object
     * @return string
     */
    public function getCacheKey(Wallet $object): string
    {
        return app(WalletService::class)
            ->getWallet($object)
            ->getKey();
    }
}
