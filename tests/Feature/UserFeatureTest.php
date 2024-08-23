<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use App\Constants\RolePermission\Constants as RolePermissionConstants;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // permissions
        Permission::create(['name' => 'view_user']);
        Permission::create(['name' => 'create_user']);
        Permission::create(['name' => 'update_user']);
        Permission::create(['name' => 'delete_user']);

        $admin_role = Role::create(['name' => RolePermissionConstants::DEFAULT_ROLES['ADMIN']]);
        $admin_role->syncPermissions(Permission::all());

        Role::create(['name' => RolePermissionConstants::DEFAULT_ROLES['ORGANIZER']]);
        Role::create(['name' => RolePermissionConstants::DEFAULT_ROLES['ATTENDEE']]);
    }

    public function test_admin_can_view_user_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(RolePermissionConstants::DEFAULT_ROLES['ADMIN']);

        $response = $this->actingAs($user)->get(route('user.index'));

        $response->assertOk();
    }

    public function test_non_admin_cannot_access_user_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('user.index'));

        $response->assertStatus(403);
    }

    public function test_user_with_access_can_view_user_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->givePermissionTo('view_user');

        $response = $this->actingAs($user)->get(route('user.index'));

        $response->assertOk();
    }

    public function test_users_are_passed_to_user_page(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(RolePermissionConstants::DEFAULT_ROLES['ADMIN']);
        User::factory(5)->create();

        $response = $this->actingAs($user)->get(route('user.index'));


        $response->assertInertia(function ($page) {
            $page->component('User/Index') // Check the correct component is being rendered
                  ->has('users', 6); // Check that 'users' has the correct count
        });
    }

    public function test_admin_can_create_a_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(RolePermissionConstants::DEFAULT_ROLES['ADMIN']);

        $response = $this->actingAs($user)->post(route('user.store'), [
            'name' => 'Test',
            'email' => 'test@email.com',
            'is_email_verified' => true,
            'password' => 'secret-password',
            'role' => RolePermissionConstants::DEFAULT_ROLES['ATTENDEE']
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');
        $this->assertCount(2, User::all());

        $new_user = User::where('email', 'test@email.com')->first();

        $this->assertEquals('Test', $new_user->name);
        $this->assertEquals('test@email.com', $new_user->email);
        $this->assertNotNull($new_user->email_verified_at);
        $this->assertTrue(Hash::check('secret-password', $new_user->password));
        $this->assertTrue($new_user->hasRole(RolePermissionConstants::DEFAULT_ROLES['ATTENDEE']));
    }

    public function test_user_without_permission_can_not_create_a_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->assertCount(1, User::all());

        $response = $this->actingAs($user)->post(route('user.store'), [
            'name' => 'Test',
            'email' => 'test@email.com',
            'is_email_verified' => true,
            'password' => 'secret-password',
            'role' => RolePermissionConstants::DEFAULT_ROLES['ATTENDEE']
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertStatus(403);

        $this->assertCount(1, User::all());
    }

    public function test_user_with_permission_can_create_a_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->givePermissionTo('create_user');

        $response = $this->actingAs($user)->post(route('user.store'), [
            'name' => 'Test',
            'email' => 'test@email.com',
            'is_email_verified' => true,
            'password' => 'secret-password',
            'role' => RolePermissionConstants::DEFAULT_ROLES['ATTENDEE']
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');
        $this->assertCount(2, User::all());

        $new_user = User::where('email', 'test@email.com')->first();

        $this->assertEquals('Test', $new_user->name);
        $this->assertEquals('test@email.com', $new_user->email);
        $this->assertNotNull($new_user->email_verified_at);
        $this->assertTrue(Hash::check('secret-password', $new_user->password));
        $this->assertTrue($new_user->hasRole(RolePermissionConstants::DEFAULT_ROLES['ATTENDEE']));
    }

    public function test_non_admin_user_with_permission_can_not_create_a_user_with_superior_role(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->givePermissionTo('create_user');

        $response = $this->actingAs($user)->post(route('user.store'), [
            'name' => 'Test',
            'email' => 'test@email.com',
            'is_email_verified' => true,
            'password' => 'secret-password',
            'role' => RolePermissionConstants::DEFAULT_ROLES['ORGANIZER']
        ]);

        $response->assertSessionHas('error');
        $this->assertCount(1, User::all());
    }

    public function test_non_admin_user_with_permission_can_create_a_user_with_equivalent_role(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->givePermissionTo('create_user');
        $user->assignRole(RolePermissionConstants::DEFAULT_ROLES['ORGANIZER']);

        $response = $this->actingAs($user)->post(route('user.store'), [
            'name' => 'Test',
            'email' => 'test@email.com',
            'is_email_verified' => true,
            'password' => 'secret-password',
            'role' => RolePermissionConstants::DEFAULT_ROLES['ORGANIZER']
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertCount(2, User::all());

        $new_user = User::where('email', 'test@email.com')->first();
        $this->assertTrue($new_user->hasRole(RolePermissionConstants::DEFAULT_ROLES['ORGANIZER']));
    }

    public function test_admin_can_update_a_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(RolePermissionConstants::DEFAULT_ROLES['ADMIN']);

        $test_user = User::factory()->create([
            'id' => 2,
            'name' => 'Test',
            'email' => 'test@email.com',
            'password' => 'secret-password',
        ]);

        $response = $this->actingAs($user)->put(route('user.update', ['user' => $test_user->id]), [
            'name' => 'TestUpdated',
            'email' => 'testupdated@email.com',
            'is_email_verified' => true,
            'password' => 'secret-password-updated',
            'role' => RolePermissionConstants::DEFAULT_ROLES['ATTENDEE']
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');
        $this->assertCount(2, User::all());

        $updated_user = User::find(2)->fresh();

        $this->assertEquals('TestUpdated', $updated_user->name);
        $this->assertEquals('testupdated@email.com', $updated_user->email);
        $this->assertNotNull($updated_user->email_verified_at);
        $this->assertTrue(Hash::check('secret-password-updated', $updated_user->password));
        $this->assertTrue($updated_user->hasRole(RolePermissionConstants::DEFAULT_ROLES['ATTENDEE']));
    }

    public function test_user_without_permission_can_not_update_a_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create();;

        $test_user = User::factory()->create([
            'id' => 2,
            'name' => 'Test',
            'email' => 'test@email.com',
            'password' => 'secret-password',
            'email_verified_at' => null
        ]);


        $response = $this->actingAs($user)->put(route('user.update', ['user' => $test_user->id]), [
            'name' => 'TestUpdated',
            'email' => 'testupdated@email.com',
            'is_email_verified' => true,
            'password' => 'secret-password-updated',
            'role' => RolePermissionConstants::DEFAULT_ROLES['ATTENDEE']
        ]);

        $response->assertStatus(403);

        $this->assertCount(2, User::all());
    }

    public function test_user_with_permission_can_update_a_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->givePermissionTo('update_user');

        $test_user = User::factory()->create([
            'id' => 2,
            'name' => 'Test',
            'email' => 'test@email.com',
            'password' => 'secret-password',
            'email_verified_at' => null
        ]);

        $response = $this->actingAs($user)->put(route('user.update', ['user' => $test_user->id]), [
            'name' => 'TestUpdated',
            'email' => 'testupdated@email.com',
            'is_email_verified' => true,
            'password' => 'secret-password-updated',
            'role' => RolePermissionConstants::DEFAULT_ROLES['ATTENDEE']
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');
        $this->assertCount(2, User::all());

        $updated_user = User::find(2);

        $this->assertEquals('TestUpdated', $updated_user->name);
        $this->assertEquals('testupdated@email.com', $updated_user->email);
        $this->assertNotNull($updated_user->email_verified_at);
        $this->assertTrue(Hash::check('secret-password-updated', $updated_user->password));
        $this->assertTrue($updated_user->hasRole(RolePermissionConstants::DEFAULT_ROLES['ATTENDEE']));
    }

    public function test_admin_can_delete_a_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(RolePermissionConstants::DEFAULT_ROLES['ADMIN']);

        $test_user = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('user.destroy', ['user' => $test_user]));

        Log::info(json_encode([
            'user' => $user->highest_role(),
            'test' => $test_user->highest_role()
        ]));

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');
        $this->assertCount(1, User::all());
        $this->assertDatabaseMissing('users', ['email' => $test_user->email]);
    }

    public function test_user_without_permission_can_not_delete_a_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create();;

        $test_user = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('user.destroy', ['user' => $test_user]));

        $response->assertStatus(403);

        $this->assertCount(2, User::all());
    }

    public function test_user_with_permission_can_delete_a_user(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->givePermissionTo('delete_user');

        $test_user = User::factory()->create();

        $response = $this->actingAs($user)->delete(route('user.destroy', ['user' => $test_user]));

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');
        $this->assertCount(1, User::all());
        $this->assertDatabaseMissing('users', ['email' => $test_user->email]);
    }
}
