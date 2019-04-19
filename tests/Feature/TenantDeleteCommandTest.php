<?php

namespace Tests\Feature;

use App\Tenant;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Notification;
use Tests\TenantAwareTestCase;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;

class TenantDeleteCommandTest extends TenantAwareTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    /** @test */
    public function tenant_name_is_required()
    {
        $this->expectExceptionMessage('Not enough arguments (missing: "name").');
        $this->artisan('tenant:delete');
    }

    /** @test */
    public function can_delete_existing_tenant()
    {
        $this->artisan('tenant:create', ['name' => 'example', 'password'=>'secret', 'email' => 'test@example.com']);
        $fqdn = 'example.'.config('tenancy.hostname.default');
        $hostname = Hostname::with('website')->where('fqdn',$fqdn)->first();
        $this->artisan('tenant:delete', ['name' => 'example']);
        $this->assertSystemDatabaseMissing('hostnames', ['fqdn' => $fqdn]);
        $this->assertSystemDatabaseMissing('websites', ['id' => $hostname->website_id]);
    }

    /** @test */
    public function tenant_database_is_removed()
    {
        $this->artisan('tenant:create', ['name' => 'example', 'email' => 'test@example.com', 'password'=>'secret']);
        $this->artisan('tenant:delete', ['name' => 'example']);
        $this->expectException(QueryException::class);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    protected function tearDown(): void
    {
        if ($tenant = Tenant::tenantExists('example')) {
            $tenant->delete();
        }
        parent::tearDown();
    }
}
