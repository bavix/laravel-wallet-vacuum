<?php

namespace Bavix\WalletVacuum\Services;

use Bavix\Wallet\Interfaces\Wallet;
use Illuminate\Database\Eloquent\Model;

class StoreService
{

    /**
     * @param Wallet $object
     * @return string
     */
    public function getCacheKey($object): string
    {
        /**
         * @var Model $object
         */
        return __METHOD__ . $object->getKey();
    }

}
