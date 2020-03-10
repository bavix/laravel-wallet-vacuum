<?php

namespace Bavix\WalletVacuum;

use Bavix\Wallet\Interfaces\Mathable;
use Bavix\Wallet\Simple\Store as Storable;
use Bavix\WalletVacuum\Services\StoreService;
use Illuminate\Support\Facades\Cache;

class Store extends Storable
{

    /**
     * Get the balance from the cache.
     *
     * @inheritDoc
     */
    public function getBalance($object)
    {
        return Cache::get(
            app(StoreService::class)->getCacheKey($object),
            parent::getBalance($object)
        );
    }

    /**
     * Increases the wallet balance in the cache array
     *
     * @inheritDoc
     */
    public function incBalance($object, $amount)
    {
        $key = app(StoreService::class)
            ->getCacheKey($object);

        if (!Cache::has($key)) {
            $this->setBalance($object, $this->getBalance($object));
        }

        $this->balanceSheets = []; // cleanup
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
    public function setBalance($object, $amount): bool
    {
        $this->balanceSheets = []; // cleanup
        return Cache::put(
            app(StoreService::class)->getCacheKey($object),
            app(Mathable::class)->round($amount),
            600
        );
    }

}
