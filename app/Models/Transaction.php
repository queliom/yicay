<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'user_id_from',
        'user_id_to',
        'amount',
        'situation',
    ];

    public static function timeSecondsSinceEquivalentProperties(array $data)
    {
        $runTime = DB::table('transactions')
            ->select(DB::raw('TIMESTAMPDIFF(SECOND, created_at, NOW()) AS seconds'))
            ->where('amount', $data['amount'])
            ->where('user_id_to',$data['payee'])
            ->where('user_id_from', $data['payer'])
            ->orderByDesc('created_at')
            ->first();
            
        return $runTime->seconds ?? false;
    }

    public static function new(array $data): string
    {
        $id = self::create([
            'id' => Str::uuid()->toString(),
            'user_id_to' => $data['payee'],
            'user_id_from' => $data['payer'],
            'amount' => $data['amount']
        ])->id;

        return $id;
    }

    public static function setSituation(string $transactionUuid, string $situation): void
    {
        self::where('id', $transactionUuid)
        ->update(['situation' => $situation]);
    }

}
