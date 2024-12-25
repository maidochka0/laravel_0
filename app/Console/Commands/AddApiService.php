<?php

namespace App\Console\Commands;

use App\Models\ApiService;
use Illuminate\Console\Command;

class AddApiService extends Command
{
    protected $signature = 'api-service:add {name}';
    protected $description = 'Add a new API service';

    public function handle()
    {
        $apiService = ApiService::create(['name' => $this->argument('name')]);
        $this->info("API service '{$apiService->name}' has been added.");
    }
}
