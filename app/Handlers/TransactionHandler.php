<?php

namespace App\Handlers;

use App\Models\Transaction;
use Illuminate\Support\Facades\Config;

class TransactionHandler 
{
    public static function timeBetweenEquivalentTransactionsIsTolerated(array $payload): bool
    {
        $seconds = Transaction::timeSecondsSinceEquivalentProperties($payload);

        if (
            is_numeric($seconds) && 
            $seconds <= 
            Config::get('constants.transactions.last_tolerated_equivalence')) {
            return false;
        }
        return true;
    }

    public static function payerItsNotPayee(string $payer, string $payee): bool
    {
        return $payer != $payee ? true : false;
    }
}