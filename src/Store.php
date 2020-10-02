<?php

namespace Bavix\WalletVacuum;

use Bavix\Wallet\Interfaces\Mathable;
use Bavix\Wallet\Interfaces\Storable;
use Bavix\WalletVacuum\Services\StoreService;
use Bavix\Wallet\Simple\Store as SimpleStore;
use Illuminate\Cache\TaggedCache;
use Illuminate\Support\Facades\Cache;

class Store implements Storable
{

    /**
     * @var array
     */
    protected $tags;

    /**
     * Store constructor.
     */
    public function __construct()
    {
        $this->tags = config('wallet-vacuum.tags', ['wallets', 'vacuum']);
    }

    /**
     * Get the balance from the cache.
     *
     * @inheritDoc
     */
    public function getBalance($object)
    {
        $key = app(StoreService::class)
            ->getCacheKey($object);

        $balance = $this->getCache()->get($key);
        if ($balance === null) {
            $balance = (new SimpleStore())
                ->getBalance($object);
        }

        return $balance;
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

        if (!$this->getCache()->has($key)) {
            $this->setBalance($object, $this->getBalance($object));
        }

        $this->getCache()->increment($key, $amount);

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
        return $this->getCache()->put(
            app(StoreService::class)->getCacheKey($object),
            app(Mathable::class)->round($amount),
            600
        );
    }

    /**
     * @return bool
     */
    public function fresh(): bool
    {
        return $this->getCache()->flush();
    }

    /**
     * @return TaggedCache
     */
    public function getCache(): TaggedCache
    {
        return Cache::tags($this->tags);
    }

}
