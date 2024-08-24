<?php

namespace Tests\Feature\RolePermission;

use App\Constants\RolePermission\Constants;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // permissions
        Permission::create(['name' => 'view_permission']);
        Permission::create(['name' => 'create_permission']);
        Permission::create(['name' => 'update_permission']);
        Permission::create(['name' => 'delete_permission']);

        $admin_role = Role::create(['name' => Constants::DEFAULT_ROLES['ADMIN']]);
        Role::create(['name' => Constants::DEFAULT_ROLES['ORGANIZER']]);
        Role::create(['name' => Constants::DEFAULT_ROLES['ATTENDEE']]);

        $admin_role->syncPermissions(Permission::all());
    }

    public function test_admin_can_view_permissions_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ADMIN']);

        $response = $this->actingAs($user)->get(route('permission.index'));

        $response->assertOk();
    }

    public function test_non_admin_cannot_access_permission_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $user_role = Role::create(['name' => 'user']);
        $user_role->syncPermissions([]);

        $user->assignRole(Constants::DEFAULT_ROLES['ORGANIZER']);

        $response = $this->actingAs($user)->get(route('permission.index'));

        $response->assertStatus(403);
    }

    public function test_all_roles_and_permissions_are_displayed_on_roles_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ADMIN']);

        $response = $this->actingAs($user)->get(route('permission.index'));

        $response->assertInertia(function ($page) {
            $page->component('RolePermission/Permission') // Check the correct component is being rendered
                ->has('roles', 3) // Check that 'roles' has the correct count
                ->has('permissions', 4);
        });
    }

    public function test_admin_can_create_a_new_permission(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ADMIN']);

        $response = $this->actingAs($user)->post(route('permission.store'), [
            'name' => 'new_permission',
        ]);

        $this->assertCount(5, Permission::all());
        $response->assertSessionHas('success');
    }

    public function test_validate_create_request(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ADMIN']);

        $response = $this->actingAs($user)->post(route('permission.store'), [
            'name' => '',
        ]);

        $this->assertCount(4, Permission::all());
        $response->assertSessionHasErrors('name');
    }

    public function test_non_admin_cannot_create_a_new_permission(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $user_role = Role::create(['name' => 'user']);
        $user_role->syncPermissions([]);

        $user->assignRole(Constants::DEFAULT_ROLES['ORGANIZER']);

        $response = $this->actingAs($user)->post(route('permission.store'), [
            'name' => 'new_permission',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_update_an_existing_permission(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ADMIN']);

        $permission = Permission::create(['name' => 'old_permission']);

        $this->assertCount(5, Permission::all());

        $response = $this->actingAs($user)->put(route('permission.update', ['permission' => $permission->id]), [
            'name' => 'updated_permission_name',
        ]);

        $this->assertCount(5, Permission::all());
        $this->assertEquals('updated_permission_name', Permission::find($permission->id)->name);
        $response->assertSessionHas('success');
    }

    public function test_validate_update_request(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ADMIN']);

        $permission = Permission::create(['name' => 'old_permission']);

        $this->assertCount(5, Permission::all());

        $response = $this->actingAs($user)->put(route('permission.update', ['permission' => $permission->id]), [
            'name' => '',
        ]);

        $this->assertCount(5, Permission::all());
        $this->assertEquals('old_permission', Permission::find($permission->id)->name);
        $response->assertSessionHasErrors('name');
    }

    public function test_non_admin_cannot_update_a_permission(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ORGANIZER']);

        $role = Role::create(['name' => 'old_name']);

        $response = $this->actingAs($user)->put(route('role.update', ['role' => $role->id]), [
            'name' => 'updated_role_name',
        ]);

        $response->assertStatus(403);
    }

    public function test_updating_permission_updates_on_all_associated_users(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ADMIN']);

        $permission = Permission::create(['name' => 'old_permission']);

        $user->givePermissionTo('old_permission');

        $this->actingAs($user)->put(route('permission.update', ['permission' => $permission->id]), [
            'name' => 'new_permission',
        ]);

        $this->assertTrue($user->fresh()->hasPermissionTo('new_permission'));
        $this->assertDatabaseMissing('permissions', ['name' => 'old_permission']);
    }

    public function test_admin_can_delete_an_existing_permission(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ADMIN']);

        $permission = Permission::create(['name' => 'test_permission']);

        $this->assertCount(5, Permission::all());

        $response = $this->actingAs($user)->delete(route('permission.destroy', ['permission' => $permission->id]));

        $this->assertCount(4, Permission::all());
        $this->assertDatabaseMissing('permissions', ['name' => 'test_permission']);
        $response->assertSessionHas('success');
    }

    public function test_non_admin_cannot_delete_a_permission(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ORGANIZER']);

        $permission = Permission::create(['name' => 'test_permission']);

        $this->assertCount(5, Permission::all());

        $response = $this->actingAs($user)->delete(route('permission.destroy', ['permission' => $permission->id]));

        $this->assertCount(5, Permission::all());
        $response->assertStatus(403);
    }
}
