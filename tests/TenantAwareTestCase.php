<?php

namespace Tests;

use App\Http\Middleware\VerifyCsrfToken;
use App\Tenant;
use App\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TenantAwareTestCase extends TestCase
{
    use RefreshDatabase;

    protected $tenants;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenants = [];
        Config::set('tenancy.website.auto-delete-tenant-directory', true);
        Notification::fake();
    }

    protected function refreshApplication()
    {
        parent::refreshApplication();
        $this->artisan('migrate:fresh --database=' . config('tenancy.db.system-connection-name'));
        $this->artisan('tenancy:migrate:refresh');
    }

    protected function assertSystemDatabaseHas($table, array $data)
    {
        $this->assertDatabaseHas($table, $data, config('tenancy.db.system-connection-name'));
    }

    protected function assertSystemDatabaseMissing($table, array $data)
    {
        $this->assertDatabaseMissing($table, $data, config('tenancy.db.system-connection-name'));
    }

    protected function tearDown(): void
    {
        foreach ($this->tenants as $tenant) {
            $tenant->deleteByFqdn($tenant->hostname->fqdn);
        }
        parent::tearDown();
    }

    protected function signIn($user = null)
    {
        $this->actingAs($user ?: $this->tenants[0]->admin);
    }

    protected function createUserInTenant($overrides = [], $tenantName = 'test')
    {
        $this->registerTenant($tenantName);

        return factory(User::class)->create($overrides);
    }

    protected function registerTenant($tenantName = 'test'): Tenant
    {
        $tenant = Tenant::registerTenant($tenantName, "admin@{$tenantName}.com", 'secret');
        array_push($this->tenants, $tenant);

        return $tenant;
    }

    protected function withoutVerifyCSRFMiddleware()
    {
        return $this->withoutMiddleware(VerifyCsrfToken::class);
    }
}
