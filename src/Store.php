<?php

namespace Bavix\WalletVacuum;

use Bavix\Wallet\Interfaces\Mathable;
use Bavix\Wallet\Interfaces\Storable;
use Bavix\Wallet\Simple\Store as SimpleStore;
use Bavix\WalletVacuum\Services\StoreService;
use Illuminate\Cache\TaggedCache;
use Illuminate\Support\Facades\Cache;

class Store implements Storable
{
    /**
     * @var array
     */
    protected $tags;

    /**
     * @var int
     */
    protected $ttl;

    /**
     * Store constructor.
     */
    public function __construct()
    {
        $this->tags = config('wallet-vacuum.tags', ['wallets', 'vacuum']);
        $this->ttl = config('wallet-vacuum.ttl', 600);
    }

    /**
     * Get the balance from the cache.
     *
     * {@inheritdoc}
     */
    public function getBalance($object)
    {
        $key = app(StoreService::class)
            ->getCacheKey($object);

        $balance = $this->taggedCache()
            ->get($key);

        if ($balance === null) {
            $balance = (new SimpleStore())
                ->getBalance($object);
        }

        return $balance;
    }

    /**
     * Increases the wallet balance in the cache array.
     *
     * {@inheritdoc}
     */
    public function incBalance($object, $amount)
    {
        $key = app(StoreService::class)
            ->getCacheKey($object);

        if (! $this->taggedCache()->has($key)) {
            $this->setBalance($object, $this->getBalance($object));
        }

        $this->taggedCache()->increment($key, $amount);

        /**
         * When your project grows to high loads and situations arise with a race condition,
         * you understand that an extra request to
         * the cache will save you from many problems when
         * checking the balance.
         */
        return $this->getBalance($object);
    }

    /**
     * sets the cache value directly.
     *
     * {@inheritdoc}
     */
    public function setBalance($object, $amount): bool
    {
        return $this->taggedCache()->put(
            app(StoreService::class)->getCacheKey($object),
            app(Mathable::class)->round($amount),
            $this->ttl
        );
    }

    /**
     * @return bool
     */
    public function fresh(): bool
    {
        return $this->taggedCache()->flush();
    }

    /**
     * @return TaggedCache
     */
    public function taggedCache(): TaggedCache
    {
        return Cache::tags($this->tags);
    }
}
