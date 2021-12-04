<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WalletPost extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'wallet_id',
        'transaction_id',
        'amount',
    ];

    public static function debitEntry(
        string $walletUuid, 
        string $transactionUuid, 
        float $amount) : void {

        self::create([
            'id' => Str::uuid()->toString(),
            'wallet_id' => $walletUuid,
            'transaction_id' => $transactionUuid,
            'amount' => -$amount
        ]);
    }

    public static function creditEntry(
        string $walletUuid, 
        string $transactionUuid, 
        float $amount) : void {
            
        self::create([
            'id' => Str::uuid()->toString(),
            'wallet_id' => $walletUuid,
            'transaction_id' => $transactionUuid,
            'amount' => $amount
        ]);
    }
}
