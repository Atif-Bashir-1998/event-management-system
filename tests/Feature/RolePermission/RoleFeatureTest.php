<?php

namespace Tests\Feature\RolePermission;

use App\Constants\RolePermission\Constants;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // permissions
        Permission::create(['name' => 'view_role']);
        Permission::create(['name' => 'create_role']);
        Permission::create(['name' => 'update_role']);
        Permission::create(['name' => 'delete_role']);

        $admin_role = Role::create(['name' => Constants::DEFAULT_ROLES['ADMIN']]);
        Role::create(['name' => Constants::DEFAULT_ROLES['ORGANIZER']]);
        Role::create(['name' => Constants::DEFAULT_ROLES['ATTENDEE']]);

        $admin_role->syncPermissions(Permission::all());
    }

    public function test_admin_can_view_user_roles_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ADMIN']);

        $response = $this->actingAs($user)->get(route('role.index'));

        $response->assertOk();
    }

    public function test_non_admin_cannot_access_role_index_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $user->assignRole(Constants::DEFAULT_ROLES['ORGANIZER']);

        $response = $this->actingAs($user)->get(route('role.index'));

        $response->assertStatus(403);
    }

    public function test_all_roles_and_permissions_are_displayed_on_roles_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ADMIN']);

        $response = $this->actingAs($user)->get(route('role.index'));

        $response->assertInertia(function ($page) {
            $page->component('RolePermission/Role') // Check the correct component is being rendered
                  ->has('roles', 3) // Check that 'roles' has the correct count
                  ->has('permissions', 4);
        });
    }

    public function test_admin_can_create_a_new_user_role(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ADMIN']);

        $response = $this->actingAs($user)->post(route('role.store'), [
            'name' => 'new_role'
        ]);

        $this->assertCount(4, Role::all());
        $response->assertSessionHas('success');
    }

    public function test_validate_create_request(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ADMIN']);

        $response = $this->actingAs($user)->post(route('role.store'), [
            'name' => ''
        ]);

        $this->assertCount(3, Role::all());
        $response->assertSessionHasErrors('name');
    }

    public function test_non_admin_cannot_create_a_new_user_role(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $user->assignRole(Constants::DEFAULT_ROLES['ORGANIZER']);

        $response = $this->actingAs($user)->post(route('role.store'), [
            'name' => 'new_role'
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_an_existing_user_role(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ADMIN']);

        $role = Role::create(['name' => 'old_name']);

        $this->assertCount(4, Role::all());

        $response = $this->actingAs($user)->put(route('role.update', ['role' => $role->id]), [
            'name' => 'updated_role_name'
        ]);

        $this->assertCount(4, Role::all());
        $this->assertEquals('updated_role_name', Role::find($role->id)->name);
        $response->assertSessionHas('success');
    }

    public function test_validate_update_request(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ADMIN']);

        $role = Role::create(['name' => 'old_name']);

        $this->assertCount(4, Role::all());

        $response = $this->actingAs($user)->put(route('role.update', ['role' => $role->id]), [
            'name' => ''
        ]);

        $this->assertCount(4, Role::all());
        $this->assertEquals('old_name', Role::find($role->id)->name);
        $response->assertSessionHasErrors('name');
    }

    public function test_non_admin_cannot_update_an_existing_user_role(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $user->assignRole(Constants::DEFAULT_ROLES['ORGANIZER']);

        $role = Role::create(['name' => 'old_name']);

        $response = $this->actingAs($user)->put(route('role.update', ['role' => $role->id]), [
            'name' => 'updated_role_name'
        ]);

        $response->assertStatus(403);
    }

    public function test_updating_role_name_updates_on_all_associated_users(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ADMIN']);
        $admin_role = Role::where('name', 'admin')->first();

        $this->actingAs($user)->put(route('role.update', ['role' => $admin_role->id]), [
            'name' => 'new_name'
        ]);

        $this->assertEquals($user->name, $user->fresh()->name);
        $this->assertEquals('new_name', $user->fresh()->getRoleNames()->first());
    }

    public function test_admin_can_delete_an_existing_user_role(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ADMIN']);

        $role = Role::create(['name' => 'old_role']);

        $this->assertCount(4, Role::all());

        $response = $this->actingAs($user)->delete(route('role.destroy', ['role' => $role->id]));

        $this->assertCount(3, Role::all());
        $this->assertDatabaseMissing('roles', ['name' => 'old_role']);
        $response->assertSessionHas('success');
    }

    public function test_admin_can_not_delete_a_role_which_has_associated_users(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ADMIN']);

        $role = Role::create(['name' => 'test_role']);
        $test_user = User::factory()->create();
        $test_user->assignRole('test_role');

        $this->assertCount(4, Role::all());

        $response = $this->actingAs($user)->delete(route('role.destroy', ['role' => $role]));

        $this->assertCount(4, Role::all());
        $response->assertSessionHas('error');
    }

    public function test_non_admin_cannot_delete_a_user_role(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $user->assignRole(Constants::DEFAULT_ROLES['ORGANIZER']);

        $role = Role::create(['name' => 'old_name']);

        $this->assertCount(4, Role::all());

        $response = $this->actingAs($user)->delete(route('role.destroy', ['role' => $role]));

        $this->assertCount(4, Role::all());
        $response->assertStatus(403);
    }
}
