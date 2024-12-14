<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class All extends Model
{
    protected $guarded = ['id'];
    private static $key = 'E6kUTYrYwZq2tN4QEtyzsbEBk3ie';
    
    public static function getPrivateKey()
    {
        return self::$key;
    }
}
