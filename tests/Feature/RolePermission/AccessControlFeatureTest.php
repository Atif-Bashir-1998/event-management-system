<?php

namespace Tests\Feature\RolePermission;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccessControlFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // permissions
        Permission::create(['name' => 'view_access_control']);
        Permission::create(['name' => 'add_user_permission']);
        Permission::create(['name' => 'remove_user_permission']);
        Permission::create(['name' => 'add_role_permission']);
        Permission::create(['name' => 'remove_role_permission']);

        $admin_role = Role::create(['name' => 'admin']);

        $admin_role->syncPermissions(Permission::all());
    }

    public function test_admin_can_view_access_control_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get(route('access-control'));

        $response->assertOk();
    }

    public function test_non_admin_cannot_access_access_control_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('access-control'));

        $response->assertStatus(403);
    }

    public function test_user_with_access_can_view_access_control_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->givePermissionTo('view_access_control');

        $response = $this->actingAs($user)->get(route('access-control'));

        $response->assertOk();
    }

    public function test_admin_can_grant_permission_to_role(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $new_role = Role::create(['name' => 'new_role']);
        $new_permission = Permission::create(['name' => 'new_permission']);

        $response = $this->actingAs($user)->post(route('role.add-permission', ['role' => $new_role]), [
            'permission' => $new_permission->name
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue($new_role->fresh()->hasPermissionTo('new_permission'));
    }

    public function test_user_without_permission_can_not_add_permission_to_role(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $new_role = Role::create(['name' => 'new_role']);
        $new_permission = Permission::create(['name' => 'new_permission']);

        $response = $this->actingAs($user)->post(route('role.add-permission', ['role' => $new_role]), [
            'permission' => $new_permission->name
        ]);

        $response->assertStatus(403);
    }

    public function test_user_with_permission_can_add_permission_to_role(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->givePermissionTo('add_role_permission');
        $new_role = Role::create(['name' => 'new_role']);
        $new_permission = Permission::create(['name' => 'new_permission']);

        $response = $this->actingAs($user)->post(route('role.add-permission', ['role' => $new_role]), [
            'permission' => $new_permission->name
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue($new_role->fresh()->hasPermissionTo('new_permission'));
    }

    public function test_admin_can_remove_permission_from_role(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $new_role = Role::create(['name' => 'new_role']);
        $new_permission = Permission::create(['name' => 'new_permission']);
        $new_role->syncPermissions($new_permission->name);

        $this->assertTrue($new_role->fresh()->hasPermissionTo('new_permission'));

        $response = $this->actingAs($user)->delete(route('role.remove-permission', ['role' => $new_role]), [
            'permission' => $new_permission->name
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertFalse($new_role->fresh()->hasPermissionTo('new_permission'));
    }

    public function test_user_without_permission_can_not_remove_permission_from_role(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $new_role = Role::create(['name' => 'new_role']);
        $new_permission = Permission::create(['name' => 'new_permission']);
        $new_role->syncPermissions($new_permission->name);

        $response = $this->actingAs($user)->delete(route('role.remove-permission', ['role' => $new_role]), [
            'permission' => $new_permission->name
        ]);

        $response->assertStatus(403);
    }

    public function test_user_with_permission_can_remove_permission_from_role(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->givePermissionTo('remove_role_permission');

        $new_role = Role::create(['name' => 'new_role']);
        $new_permission = Permission::create(['name' => 'new_permission']);
        $new_role->syncPermissions($new_permission->name);

        $response = $this->actingAs($user)->delete(route('role.remove-permission', ['role' => $new_role]), [
            'permission' => $new_permission->name
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertFalse($new_role->fresh()->hasPermissionTo('new_permission'));
    }

    public function test_admin_can_grant_permission_to_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('admin');
        $other_user = User::factory()->create();

        $new_permission = Permission::create(['name' => 'new_permission']);

        $response = $this->actingAs($user)->post(route('user.add-permission', ['user' => $other_user]), [
            'permission' => $new_permission->name
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue($other_user->fresh()->hasPermissionTo('new_permission'));
    }

    public function test_user_without_permission_can_not_add_permission_to_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $other_user = User::factory()->create();
        $new_permission = Permission::create(['name' => 'new_permission']);

        $response = $this->actingAs($user)->post(route('user.add-permission', ['user' => $other_user]), [
            'permission' => $new_permission->name
        ]);

        $response->assertStatus(403);
    }

    public function test_user_with_permission_can_add_permission_to_user(): void
    {
        /** @var User $user_with_permission */
        $user_with_permission = User::factory()->create();
        $user_with_permission->givePermissionTo('add_user_permission');

        /** @var User $test_user */
        $test_user = User::factory()->create();
        $new_permission = Permission::create(['name' => 'new_permission']);

        $response = $this->actingAs($user_with_permission)->post(route('user.add-permission', ['user' => $test_user]), [
            'permission' => $new_permission->name
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertTrue($test_user->fresh()->hasPermissionTo('new_permission'));
    }

    public function test_admin_can_remove_permission_from_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('admin');

        $other_user = User::factory()->create();
        $new_permission = Permission::create(['name' => 'new_permission']);
        $other_user->givePermissionTo($new_permission->name);

        $this->assertTrue($other_user->fresh()->hasPermissionTo('new_permission'));

        $response = $this->actingAs($user)->delete(route('user.remove-permission', ['user' => $other_user]), [
            'permission' => $new_permission->name
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertFalse($other_user->fresh()->hasPermissionTo('new_permission'));
    }

    public function test_user_without_permission_can_not_remove_permission_from_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $other_user = User::factory()->create();
        $new_permission = Permission::create(['name' => 'new_permission']);
        $other_user->givePermissionTo($new_permission->name);

        $response = $this->actingAs($user)->delete(route('user.remove-permission', ['user' => $other_user]), [
            'permission' => $new_permission->name
        ]);

        $response->assertStatus(403);
    }

    public function test_user_with_permission_can_remove_permission_from_user(): void
    {
        /** @var User $user_with_permission */
        $user_with_permission = User::factory()->create();
        $user_with_permission->givePermissionTo('remove_user_permission');

        /** @var User $test_user */
        $test_user = User::factory()->create();
        $new_permission = Permission::create(['name' => 'new_permission']);
        $test_user->givePermissionTo('new_permission');

        $response = $this->actingAs($user_with_permission)->delete(route('user.remove-permission', ['user' => $test_user]), [
            'permission' => $new_permission->name
        ]);

        $this->assertFalse($test_user->fresh()->hasPermissionTo('new_permission'));
        $response->assertSessionHasNoErrors();
    }
}
