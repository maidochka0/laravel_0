<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;

class AddCompany extends Command
{
    protected $signature = 'company:add {name}';
    protected $description = 'add new company';

    public function handle()
    {
        $company = Company::create(['name' => $this->argument('name')]);
        $this->info("company '{$company->name}' added.");
    }
}
