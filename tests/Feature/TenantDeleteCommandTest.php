<?php

namespace Tests\Feature;

use App\Notifications\TenantCreated;
use App\Tenant;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TenantDeleteCommandTest extends TestCase
{
    protected function setUp()
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
        $this->artisan('tenant:create', ['name' => 'example', 'email' => 'test@example.com']);
        $this->artisan('tenant:delete', ['name' => 'example']);
        $this->assertSystemDatabaseMissing('customers', ['email' => 'test@example.com']);
    }

    /** @test */
    public function tenant_database_is_removed()
    {
        $this->artisan('tenant:create', ['name' => 'example', 'email' => 'test@example.com']);
        $this->artisan('tenant:delete', ['name' => 'example']);
        $this->expectException(QueryException::class);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    protected function tearDown()
    {
        if ($tenant = Tenant::retrieveBy('example')) {
            $tenant->delete();
        }
        parent::tearDown();
    }
}
