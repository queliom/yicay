<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $incrementing = false;

    public static function getUserFunds(string $userUuid): float
    {   
        $balance = self::where('user_id', $userUuid)
            ->get()
            ->first()
            ->balance;
            
        return $balance;
    }

    public static function userDiscount(string $userUuid, float $discount): void
    {   
        self::where('user_id', $userUuid)
            ->decrement('balance', $discount);
    }

    public static function userCredit(string $userUuid, float $credit) : void
    {
        self::where('user_id', $userUuid)
            ->increment('balance', $credit);
    }

    public static function getIdByUser(string $userUuid): string
    {
        $userWalletId = self::where('user_id', $userUuid)
            ->get()
            ->first()
            ->id;
        
        return $userWalletId;
    }
    
}
