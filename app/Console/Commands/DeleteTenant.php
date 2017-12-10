<?php

namespace App\Console\Commands;

use Hyn\Tenancy\Contracts\Repositories\CustomerRepository;
use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class DeleteTenant extends Command
{
    protected $signature = 'tenant:delete {name}';
    protected $description = 'Deletes a tenant of the provided name. Only available on the local environment e.g. php artisan tenant:delete boise';

    public function handle()
    {
        // because this is a destructive command, we'll only allow to run this command
        // if you are on the local environment
        if (!app()->isLocal()) {
            $this->error('This command is only avilable on the local environment.');

            return;
        }

        $name = $this->argument('name');
        $this->deleteTenant($name);
    }

    private function deleteTenant($name)
    {
        if ($customer = Customer::where('name', $name)->with(['websites', 'hostnames'])->firstOrFail()) {
            $hostname = $customer->hostnames->first();
            $website = $customer->websites->first();
            app(HostnameRepository::class)->delete($hostname, true);
            app(WebsiteRepository::class)->delete($website, true);
            app(CustomerRepository::class)->delete($customer, true);
            $this->info("Tenant {$name} successfully deleted.");
        }
    }
}
