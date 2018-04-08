<?php

namespace Tests\Feature;

use App\Tenant;
use App\User;
use Hyn\Tenancy\Environment;
use Tests\TenantAwareTestCase;

class ProfileTest extends TenantAwareTestCase
{
    /** @test */
    public function edit_renders_correct_view()
    {
        $user = $this->createUserInTenant();
        $this->signIn($user);
        $response = $this->get(route('tenant.account.profile.edit'));
        $response->assertViewIs('tenant.account.profile');
    }

    /** @test */
    public function admin_can_browse_profile_edit_page()
    {
        $this->createUserInTenant();
        $this->signIn();
        $response = $this->get(route('tenant.account.profile.edit'));
        $response->assertViewIs('tenant.account.profile');
    }

    /** @test */
    public function non_admin_can_browse_profile_edit_page()
    {
        $user = $this->createUserInTenant();
        $this->signIn($user);
        $response = $this->get(route('tenant.account.profile.edit'));
        $response->assertViewIs('tenant.account.profile');
    }

    /** @test */
    public function unauthenticated_user_cannot_browse_profile_edit_page()
    {
        $this->registerTenant();
        $this->get(route('tenant.account.profile.edit'))
            ->assertRedirect('/login');
    }

    /** @test */
    public function name_is_required()
    {
        $user = $this->createUserInTenant();
        $this->signIn($user);
        $this->update(['email' => $user->email])
            ->assertSessionHasErrors(['name' => 'The name field is required.']);
    }

    /** @test */
    public function email_is_required()
    {
        $user = $this->createUserInTenant();
        $this->signIn($user);
        $this->update(['name' => 'Test Name'])
            ->assertSessionHasErrors(['email' => 'The email field is required.']);
    }

    /** @test */
    public function email_should_be_unique()
    {
        $user1 = $this->createUserInTenant();
        $user2 = factory(User::class)->create();
        $this->signIn($user1);

        // updating to an email address that belongs to someone else should fail uniquenss test
        $this->update(['name' => 'First Name', 'email' => $user2->email])
            ->assertSessionHasErrors(['email' => 'The email has already been taken.']);

        // updating to a new email address should pass uniquenss test
        $this->withoutExceptionHandling()
            ->update(['name' => 'First Name', 'email' => 'somerandomemail@example.com']);

        // updating to own new email address should pass uniquenss test
        $this->withoutExceptionHandling()
            ->update(['name' => 'New Name', 'email' => $user1->email]);
    }

    /** @test */
    public function must_be_authenticated_to_update_profile()
    {
        $this->createUserInTenant();
        $this->update(['name' => 'new name', 'email' => 'new email'])->assertRedirect('/login');
    }


    /** @test */
    public function user_information_is_udpated_in_database()
    {
        $user = $this->createUserInTenant();
        $this->signIn($user);

        $this->update(['name' => 'new name', 'email' => 'new email']);
        $this->assertDatabaseHas('users', ['name' => 'new name', 'email' => 'new email']);
    }

    /** @test */
    public function user_is_updated_only_in_tenants_own_database()
    {
        $user1 = $this->createUserInTenant([], 'tenant1');
        $user2 = $this->createUserInTenant([], 'tenant2');

        $this->withoutExceptionHandling();
        // update user from the first tenant
        $this->switchTenant($this->tenants[0])->signIn($user1);
        $this->update(['email' => 'newemail@tenant.com', 'name' => $user1->name]);
        $this->assertDatabaseHas('users', ['email' => 'newemail@tenant.com', 'name' => $user1->name]);

        // shouldn't update user from the second tenant
        $this->switchTenant($this->tenants[1]);
        $this->assertDatabaseHas('users', ['email' => $user2->email, 'name' => $user2->name]);
    }

    /** @test */
    public function profile_update_success_message_is_flashed()
    {
        $user = $this->createUserInTenant();
        $this->signIn($user);

        $this->update(['name' => 'new name', 'email' => 'new email'])
            ->assertSessionHas('alert', ['type' => 'success', 'message' => 'Your profile has been updated.']);
    }

    /** @test */
    public function unsuccessful_profile_update_success_message_is_not_flashed()
    {
        $user = $this->createUserInTenant();
        $this->signIn($user);
        $this->update(['email' => $user->email])
            ->assertSessionMissing('alert', ['type' => 'success', 'message' => 'Your profile has been updated.']);
    }

    /** @test */
    public function user_is_redirected_to_profile_edit_page_after_update()
    {
        $user = $this->createUserInTenant();
        $this->signIn($user);

        $this->update(['name' => 'new name', 'email' => 'new email'])
            ->assertRedirect(route('tenant.account.profile.edit'));
    }

    private function update($attributes)
    {
        return $this->withoutVerifiyCSRFMiddleware()->patch(route('tenant.account.profile.update'), $attributes);
    }

    private function switchTenant(Tenant $tenant)
    {
        app(Environment::class)->hostname($tenant->hostname);

        return $this;
    }
}
