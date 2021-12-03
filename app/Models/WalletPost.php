<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletPost extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'wallet_id',
        'transaction_id',
        'wallet_balance_before',
        'wallet_balance_after',
        'amount',
    ];
}
