<?php

namespace Bavix\WalletVacuum\Test;

use Bavix\Wallet\Interfaces\Mathable;
use Bavix\Wallet\Interfaces\Storable;
use Bavix\Wallet\Test\Factories\BuyerFactory;
use Bavix\Wallet\Test\Models\Buyer;
use Bavix\WalletVacuum\Services\StoreService;
use Bavix\WalletVacuum\Store;

class VacuumTest extends TestCase
{
    /**
     * @return void
     * @throws
     */
    public function testDeposits(): void
    {
        /**
         * @var Buyer[] $buyers
         */
        $buyers = BuyerFactory::times(5)->create();
        foreach ($buyers as $buyer) {
            $amount = random_int(1, 1000);
            $buyer->deposit($amount);
            self::assertEquals($amount, $buyer->balance);
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testHasWalletFloat(): void
    {
        /**
         * @var Buyer $buyer
         */
        $buyer = BuyerFactory::new()->create();
        $math = app(Mathable::class);
        /**
         * @see https://github.com/bavix/laravel-wallet-vacuum/issues/5
         */
        $buyer->wallet->decimal_places = 6;
        $buyer->wallet->save();

        $amount = '1000000000000000000.000000';
        $buyer->wallet->depositFloat($amount);
        self::assertEquals(0, $math->compare($amount, $buyer->wallet->balanceFloat));
    }

    /**
     * @return void
     * @throws
     */
    public function testFresh(): void
    {
        /**
         * @var Buyer[] $buyers
         */
        $buyers = BuyerFactory::times(5)->create();
        foreach ($buyers as $buyer) {
            $amount = random_int(1, 1000);
            $buyer->deposit($amount);
            self::assertEquals($amount, $buyer->balance);

            // fake amount to Store
            $key = app(StoreService::class)
                ->getCacheKey($buyer);

            /**
             * @var Store $store
             */
            $store = app(Storable::class);
            self::assertTrue($store->taggedCache()->put($key, $amount + 100));
            self::assertNotEquals($amount, $buyer->balance);
            self::assertEquals($amount + 100, $buyer->balance);

            self::assertTrue($store->fresh());
            self::assertEquals($amount, $buyer->balance);
        }
    }

    /**
     * @return void
     * @throws
     */
    public function testWarmUp(): void
    {
        /**
         * @var Buyer[] $buyers
         */
        $amounts = [];
        $buyers = BuyerFactory::times(5)->create();
        foreach ($buyers as $buyer) {
            $amount = random_int(1, 1000);
            $buyer->deposit($amount);
            self::assertEquals($amount, $buyer->balance);
            $amounts[$buyer->getKey()] = $amount;

            // fake amount to Store
            $key = app(StoreService::class)
                ->getCacheKey($buyer);

            /**
             * @var Store $store
             */
            $store = app(Storable::class);
            self::assertTrue($store->taggedCache()->put($key, $amount + 100));
            self::assertNotEquals($amount, $buyer->balance);
            self::assertEquals($amount + 100, $buyer->balance);
        }

        // wallet refresh balance & write to store
        $this->artisan('wallet:warm-up');

        foreach ($buyers as $buyer) {
            self::assertEquals($amounts[$buyer->getKey()], $buyer->balance);
        }
    }
}
