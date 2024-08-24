<?php

namespace Tests\Unit;

use App\Constants\RolePermission\Constants;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
// use PHPUnit\Framework\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected $policy;

    public function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'view_user']);
        Permission::create(['name' => 'create_user']);
        Permission::create(['name' => 'update_user']);
        Permission::create(['name' => 'delete_user']);

        $admin_role = Role::create(['name' => Constants::DEFAULT_ROLES['ADMIN']]);
        Role::create(['name' => Constants::DEFAULT_ROLES['ORGANIZER']]);
        Role::create(['name' => Constants::DEFAULT_ROLES['ATTENDEE']]);

        $admin_role->syncPermissions(Permission::all());

        $this->policy = new UserPolicy;
    }

    public function test_user_with_admin_role_view_any_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ADMIN']);

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_user_without_permission_cannot_view_user()
    {
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ATTENDEE']);

        $test_user = User::factory()->create();

        $this->assertFalse($this->policy->view($user, $test_user));
    }

    public function test_user_with_admin_role_create_new_user()
    {
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ADMIN']);

        $this->assertTrue($this->policy->create($user));
    }

    public function test_user_with_create_permission_create_new_user()
    {
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ATTENDEE']);
        $user->givePermissionTo('create_user');

        $this->assertTrue($this->policy->create($user));
    }

    public function test_user_without_create_permission_cannot_create_new_user()
    {
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ATTENDEE']);

        $this->assertFalse($this->policy->create($user));
    }

    public function test_user_with_admin_role_update_a_user()
    {
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ADMIN']);

        $test_user = User::factory()->create();
        $test_user->assignRole(Constants::DEFAULT_ROLES['ORGANIZER']);

        $this->assertTrue($this->policy->update($user, $test_user));
    }

    public function test_user_with_update_permission_update_a_user()
    {
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ORGANIZER']);
        $user->givePermissionTo('update_user');

        $test_user = User::factory()->create();
        $test_user->assignRole(Constants::DEFAULT_ROLES['ATTENDEE']);

        $this->assertTrue($this->policy->update($user, $test_user));
    }

    public function test_user_without_update_permission_cannot_update_a_user()
    {
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ORGANIZER']);

        $test_user = User::factory()->create();
        $test_user->assignRole(Constants::DEFAULT_ROLES['ATTENDEE']);

        $this->assertFalse($this->policy->update($user, $test_user));
    }

    public function test_user_with_admin_role_delete_a_user()
    {
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ADMIN']);

        $test_admin_user = User::factory()->create();
        $test_admin_user->assignRole(Constants::DEFAULT_ROLES['ADMIN']);

        $test_attendee_user = User::factory()->create();
        $test_attendee_user->assignRole(Constants::DEFAULT_ROLES['ATTENDEE']);

        $test_organizer_user = User::factory()->create();
        $test_organizer_user->assignRole(Constants::DEFAULT_ROLES['ORGANIZER']);

        $this->assertTrue($this->policy->delete($user, $test_admin_user));
        $this->assertTrue($this->policy->delete($user, $test_attendee_user));
        $this->assertTrue($this->policy->delete($user, $test_organizer_user));
    }

    public function test_user_with_delete_permission_delete_a_user()
    {
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ORGANIZER']);
        $user->givePermissionTo('delete_user');

        $test_user = User::factory()->create();
        $test_user->assignRole(Constants::DEFAULT_ROLES['ATTENDEE']);

        $this->assertTrue($this->policy->delete($user, $test_user));
    }

    public function test_user_without_delete_permission_cannot_delete_a_role()
    {
        $user = User::factory()->create();
        $user->assignRole(Constants::DEFAULT_ROLES['ORGANIZER']);

        $test_user = User::factory()->create();
        $test_user->assignRole(Constants::DEFAULT_ROLES['ATTENDEE']);

        $this->assertFalse($this->policy->delete($user, $test_user));
    }
}
