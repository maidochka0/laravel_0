<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Company;
use Illuminate\Console\Command;

class AddAccount extends Command
{
    protected $signature = 'account:add {company_id} {name}';
    protected $description = 'Add a new account';

    public function handle()
    {
        $company = Company::find($this->argument('company_id'));
        if (!$company) {
            $this->error('Company not found.');
            return;
        }

        $account = Account::create([
            'company_id' => $company->id,
            'name' => $this->argument('name')
        ]);

        $this->info("Account '{$account->name}' has been added to company '{$company->name}'.");
    }
}
