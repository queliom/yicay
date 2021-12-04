<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ConfirmationMessageQueue extends Model
{
    use HasFactory;

    protected $table = 'confirmation_message_queue';

    protected $fillable = [
        'id',
        'mtype',
        'body',
    ];

    public $timestamps = false;

    public static function add(string $type, string $body)
    {
        self::create([
            'id' => Str::uuid()->toString(),
            'mtype' => $type,
            'body' => $body
        ]);
    }
}
