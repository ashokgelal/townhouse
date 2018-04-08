<?php

namespace Tests;

use App\Http\Middleware\VerifyCsrfToken;
use App\Tenant;
use App\User;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TenantAwareTestCase extends TestCase
{
    use RefreshDatabase;

    protected $tenants;

    protected function setUp()
    {
        parent::setUp();
        $this->tenants = [];
        Notification::fake();
    }

    protected function refreshApplication()
    {
        parent::refreshApplication();
        $this->artisan('tenancy:install');
    }

    protected function assertSystemDatabaseHas($table, array $data)
    {
        $this->assertDatabaseHas($table, $data, env('DB_CONNECTION'));
    }

    protected function assertSystemDatabaseMissing($table, array $data)
    {
        $this->assertDatabaseMissing($table, $data, env('DB_CONNECTION'));
    }

    protected function tearDown()
    {
        foreach ($this->tenants as $tenant) {
            $tenant->delete();
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
        $tenant = Tenant::createFrom($tenantName, "admin@{$tenantName}.com", 'secret');
        array_push($this->tenants, $tenant);

        return $tenant;
    }

    protected function withoutVerifiyCSRFMiddleware()
    {
        return $this->withoutMiddleware(VerifyCsrfToken::class);
    }
}
