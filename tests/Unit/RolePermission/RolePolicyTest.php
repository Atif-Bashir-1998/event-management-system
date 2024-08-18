<?php

namespace Tests\Unit\RolePermission;

use App\Models\User;
use App\Policies\RolePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected $policy;

    public function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'view_role']);
        Permission::create(['name' => 'create_role']);
        Permission::create(['name' => 'update_role']);
        Permission::create(['name' => 'delete_role']);

        $admin_role = Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);

        $admin_role->syncPermissions(Permission::all());

        $this->policy = new RolePolicy();
    }

    public function test_user_with_admin_role_view_any_roles(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_user_without_view_role_permission_cannot_view_roles()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->assertFalse($this->policy->view($user));
    }

    public function test_user_with_admin_role_create_new_role()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertTrue($this->policy->create($user));
    }

    public function test_user_with_create_permission_create_new_role()
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $user->givePermissionTo('create_role');

        $this->assertTrue($this->policy->create($user));
    }

    public function test_user_without_create_permission_cannot_create_new_role()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->assertFalse($this->policy->create($user));
    }

    public function test_user_with_admin_role_update_a_role()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertTrue($this->policy->update($user));
    }

    public function test_user_with_update_permission_update_a_role()
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $user->givePermissionTo('update_role');

        $this->assertTrue($this->policy->update($user));
    }

    public function test_user_without_update_permission_cannot_update_a_role()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->assertFalse($this->policy->update($user));
    }

    public function test_user_with_admin_role_delete_a_role()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertTrue($this->policy->delete($user));
    }

    public function test_user_with_delete_permission_delete_a_role()
    {
        $user = User::factory()->create();
        $user->assignRole('user');
        $user->givePermissionTo('delete_role');

        $this->assertTrue($this->policy->delete($user));
    }

    public function test_user_without_delete_permission_cannot_delete_a_role()
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->assertFalse($this->policy->delete($user));
    }
}
