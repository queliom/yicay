<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
