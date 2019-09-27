<?php

namespace Bavix\WalletVacuum;

use Bavix\Wallet\Interfaces\Storable;
use Bavix\Wallet\Services\WalletService;
use Bavix\WalletVacuum\Services\StoreService;
use Illuminate\Support\Facades\Cache;

class Store implements Storable
{

    /**
     * Get the balance from the cache.
     *
     * @inheritDoc
     */
    public function getBalance($object): int
    {
        return Cache::get(
            app(StoreService::class)->getCacheKey($object),
            (int)app(WalletService::class)
                ->getWallet($object)
                ->getOriginal('balance', 0)
        );
    }

    /**
     * Increases the wallet balance in the cache array
     *
     * @inheritDoc
     */
    public function incBalance($object, int $amount): int
    {
        $key = app(StoreService::class)
            ->getCacheKey($object);

        if (!Cache::has($key)) {
            $this->setBalance($object, $this->getBalance($object));
        }

        Cache::increment($key, $amount);

        /**
         * When your project grows to high loads and situations arise with a race condition,
         * you understand that an extra request to
         * the cache will save you from many problems when
         * checking the balance.
         */
        return $this->getBalance($object);
    }

    /**
     * sets the cache value directly
     *
     * @inheritDoc
     */
    public function setBalance($object, int $amount): bool
    {
        return Cache::put(
            app(StoreService::class)->getCacheKey($object),
            $amount,
            600
        );
    }

}
