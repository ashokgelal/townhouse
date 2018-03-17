<?php

namespace App\Console\Commands;

use App\Tenant;
use Hyn\Tenancy\Models\Customer;
use Illuminate\Console\Command;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create {name} {email}';

    protected $description = 'Creates a tenant with the provided name and email address e.g. php artisan tenant:create boise boise@example.com';

    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');

        if ($this->tenantExists($name, $email)) {
            $this->error("A tenant with name '{$name}' and/or '{$email}' already exists.");

            return;
        }

        $tenant = Tenant::createFrom($name, $email);
        $this->info("Tenant '{$name}' is created and is now accessible at {$tenant->hostname->fqdn}");
        $this->info("Admin {$email} has been invited!");
    }

    private function tenantExists($name, $email): bool
    {
        return Customer::where('name', $name)->orWhere('email', $email)->exists();
    }
}
