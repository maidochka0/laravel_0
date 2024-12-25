<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    use HasFactory;

    protected $fillable = ['account_id', 'token_type_id', 'api_service_id', 'value', 'expires_at'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function tokenType()
    {
        return $this->belongsTo(TokenType::class);
    }

    public function apiService()
    {
        return $this->belongsTo(ApiService::class);
    }

}
