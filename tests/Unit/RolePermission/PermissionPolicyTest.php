<?php

namespace Tests\Unit\RolePermission;

use App\Models\User;
use App\Policies\RolePermission\PermissionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected $policy;

    public function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'view_permission']);
        Permission::create(['name' => 'create_permission']);
        Permission::create(['name' => 'update_permission']);
        Permission::create(['name' => 'delete_permission']);

        $admin_role = Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);

        $admin_role->syncPermissions(Permission::all());

        $this->policy = new PermissionPolicy();
    }

    public function test_user_with_admin_role_view_any_permission(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_user_without_view_permission_cannot_view_permissions()
    {
        $user = User::factory()->create();

        $this->assertFalse($this->policy->view($user));
    }

    public function test_user_with_view_permission_cannot_view_permissions()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('view_permission');

        $this->assertTrue($this->policy->view($user));
    }

    public function test_user_with_admin_role_create_new_permission()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertTrue($this->policy->create($user));
    }

    public function test_user_with_create_permission_create_new_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('create_permission');

        $this->assertTrue($this->policy->create($user));
    }

    public function test_user_without_create_permission_cannot_create_new_permission()
    {
        $user = User::factory()->create();

        $this->assertFalse($this->policy->create($user));
    }

    public function test_user_with_admin_role_update_a_permission()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertTrue($this->policy->update($user));
    }

    public function test_user_with_update_permission_update_a_permission()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('update_permission');

        $this->assertTrue($this->policy->update($user));
    }

    public function test_user_without_update_permission_cannot_update_a_permission()
    {
        $user = User::factory()->create();

        $this->assertFalse($this->policy->update($user));
    }

    public function test_user_with_admin_role_delete_a_permission()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->assertTrue($this->policy->delete($user));
    }

    public function test_user_with_delete_permission_delete_a_role()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('delete_permission');

        $this->assertTrue($this->policy->delete($user));
    }

    public function test_user_without_delete_permission_cannot_delete_a_role()
    {
        $user = User::factory()->create();

        $this->assertFalse($this->policy->delete($user));
    }
}
