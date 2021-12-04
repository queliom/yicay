<?php

namespace App\Handlers;

use App\Models\Wallet;

class WalletHandler 
{
    public static function payerHasSufficientFunds(string $userUuid, float $discount): bool 
    {
        $funds = Wallet::getUserFunds($userUuid);
        return $funds >= $discount ? true : false;
    }
}