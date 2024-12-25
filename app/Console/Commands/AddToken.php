<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\ApiService;
use App\Models\Token;
use App\Models\TokenType;
use Illuminate\Console\Command;

class AddToken extends Command
{
    protected $signature = 'token:add {account_id} {api_service_id} {token_type_name} {value} {expires_at?}';
    protected $description = 'Add a new token';

    public function handle()
    {
        $account = Account::find($this->argument('account_id'));
        if (!$account) {
            $this->error('Account not found.');
            return;
        }

        $apiService = ApiService::find($this->argument('api_service_id'));
        if (!$apiService) {
            $this->error('API service not found.');
            return;
        }

        // в таблице типов, имена повторяются для разных апи-сервисов, поэтому проверка немного муторная
        $tokenType = TokenType::where('name', $this->argument('token_type_name'))
            ->where('api_service_id', $apiService->id)
            ->first();

        if (!$tokenType) {
            $this->error('Token type not found for the specified API service.');
            return;
        }

        $token = Token::create([
            'account_id' => $account->id,
            'token_type_id' => $tokenType->id,
            'api_service_id' => $apiService->id,
            'value' => $this->argument('value'),
            'expires_at' => $this->argument('expires_at') ? now()->parse($this->argument('expires_at')) : null
        ]);

        $this->info("Token '{$token->value}' added for account '{$account->name}'.");
    }
}
