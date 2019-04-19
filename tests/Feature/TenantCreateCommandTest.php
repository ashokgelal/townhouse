<?php

namespace Tests\Feature;

use App\Notifications\TenantCreated;
use App\Tenant;
use App\User;
use Hyn\Tenancy\Models\Hostname;
use Illuminate\Support\Facades\Notification;
use Tests\TenantAwareTestCase;

class TenantCreateCommandTest extends TenantAwareTestCase
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
        $this->artisan('tenant:create', ['email' => 'test@example.com', 'password'=>'secret']);
    }

    /** @test */
    public function tenant_email_is_required()
    {
        $this->expectExceptionMessage('Not enough arguments (missing: "email").');
        $this->artisan('tenant:create', ['name' => 'example', 'password'=>'secret']);
    }

    /** @test */
    public function tenant_password_is_required()
    {
        $this->expectExceptionMessage('Not enough arguments (missing: "password").');
        $this->artisan('tenant:create', ['name' => 'example', 'email' => 'test@example.com']);
    }

    /** @test */
    public function can_create_new_tenant()
    {
        $this->artisan('tenant:create', ['name' => 'example', 'password'=>'secret', 'email' => 'test@example.com']);
        $fqdn = 'example.'.env('app_url_base');
        $this->assertSystemDatabaseHas('hostnames', ['fqdn' => $fqdn]);
        $hostname = Hostname::with('website')->where('fqdn',$fqdn)->first();
        $this->assertSystemDatabaseHas('websites', ['id' => $hostname->website_id]);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    /** @test */
    public function tenant_has_admin()
    {
        $this->artisan('tenant:create', ['name' => 'example', 'password'=>'secret', 'email' => 'test@example.com']);
        $this->assertDatabaseHas('users', ['email' =>  'test@example.com']);
    }

    /** @test */
    public function admin_has_proper_roles()
    {
        $this->artisan('tenant:create', ['name' => 'example', 'email' => 'test@example.com', 'password'=>'secret']);
        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasPermissionTo('edit user'));
        $this->assertTrue($user->hasPermissionTo('create user'));
        $this->assertTrue($user->hasPermissionTo('delete user'));
    }

    /** @test */
    public function admin_is_invited()
    {
        $this->artisan('tenant:create', ['name' => 'example', 'email' => 'test@example.com', 'password'=>'secret']);
        Notification::assertSentTo(User::where('email', 'test@example.com')->get(), TenantCreated::class);
    }

    protected function tearDown(): void
    {
        if ($tenant = Tenant::tenantExists('example')) {
            Tenant::delete('example');
        }
        parent::tearDown();
    }
}
