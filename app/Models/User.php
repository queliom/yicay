<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'email',
        'document',
        'utype',
        'password',
    ];

    protected $hidden = [
        'password',
    ];
    
    public static function exists(string $userUuid): bool
    {
        $hasUser = self::where('id', $userUuid)
            ->first();
            
        return $hasUser ? true : false;
    }

    public static function isConsumer(string $userUuid): bool
    {   
        $userIsConsumer = self::where('id', $userUuid)
            ->where('utype', 'consumer')
            ->first();
        
        return $userIsConsumer ? true : false;
    }

}
