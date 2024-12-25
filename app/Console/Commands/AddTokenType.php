<?php

namespace App\Console\Commands;

use App\Models\ApiService;
use App\Models\TokenType;
use Illuminate\Console\Command;

class AddTokenType extends Command
{
    protected $signature = 'token-type:add {api_service_id} {name}';
    protected $description = 'Add a new token type';

    public function handle()
    {
        $apiService = ApiService::find($this->argument('api_service_id'));
        if (!$apiService) {
            $this->error('API service not found.');
            return;
        }

        $tokenType = TokenType::create([
            'api_service_id' => $apiService->id,
            'name' => $this->argument('name')
        ]);

        $this->info("Token type '{$tokenType->name}' added for API service '{$apiService->name}'.");
    }
}
