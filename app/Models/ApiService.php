<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiService extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function tokenTypes()
    {
        return $this->hasMany(TokenType::class);
    }
}
