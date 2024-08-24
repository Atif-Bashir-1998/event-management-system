<?php

namespace App\Http\Controllers;

use App\Constants\RolePermission\Constants;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view_user', only: ['index']),
            new Middleware('can:create_user', only: ['store']),
            new Middleware('can:update_user', only: ['update']),
            new Middleware('can:delete_user', only: ['destroy']),
        ];
    }

    public function index()
    {
        $users = User::with(['roles.permissions', 'permissions'])->get();
        $roles = Role::all();
        $permissions = Permission::all();

        $users = $users->map(function ($user) use ($permissions) {
            $user->highest_role = $user->highest_role() ? $user->highest_role()->name : null;

            $user->unassigned_permissions = $permissions->diff($user->getAllPermissions());
            return $user;
        });

        return Inertia::render('User/Index', [
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|unique:users',
            'is_email_verified' => 'boolean',
            'password' => 'required|string',
            'roles' => 'sometimes|array', // Expect an array of roles
            'roles.*' => 'string' // Each item in the roles array must be a string
        ]);

        $current_user = $request->user();

        // Check each role in the array
        foreach ($validated['roles'] as $role_name) {
            $role = Role::findByName($role_name);

            if (!$role) {
                return back()->with('error', "The role {$role_name} does not exist.");
            }

            if (!$this->has_sufficient_role_level($current_user, $role)) {
                return back()->with('error', 'You do not have permission to assign a role superior to your own.');
            }
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'email_verified_at' => $validated['is_email_verified'] ? now() : null,
            'password' => Hash::make($validated['password'])
        ]);

        // Remove all previous roles and assign new roles
        $user->syncRoles($validated['roles']);

        return back()->with('success', 'User created successfully');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|string|unique:users,email,$user->id", // Exclude the current user's email from the unique check
            'is_email_verified' => 'boolean',
            'password' => 'nullable|string|min:6', // Validate password only if it's present
            'roles' => 'sometimes|array', // Expect an array of roles
            'roles.*' => 'string' // Each item in the roles array must be a string
        ]);

        $current_user = $request->user();

        // Check each role in the array
        foreach ($validated['roles'] as $role_name) {
            $role = Role::findByName($role_name);

            if (!$role) {
                return back()->with('error', "The role {$role_name} does not exist.");
            }

            if (!$this->has_sufficient_role_level($current_user, $role)) {
                return back()->with('error', 'You do not have permission to assign a role superior to your own.');
            }
        }

        // Remove all previous roles and assign new roles
        $user->syncRoles($validated['roles']);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'email_verified_at' => $validated['is_email_verified'] ? now() : null,
            'password' => $request->filled('password') ? Hash::make($validated['password']) : $user->password, // Update password only if provided
        ]);

        return back()->with('success', 'User updated successfully');
    }

    public function destroy(Request $request, User $user)
    {
        if(!$this->has_sufficient_role_level($request->user(), $user->highest_role()))
        {
            return back()->with('error', 'You do not have permission to delete a user with a role superior to your own.');
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully');
    }

    private function has_sufficient_role_level(User $current_user, Role $target_role): bool
    {
        $highest_current_role = $current_user->highest_role();

        return Constants::ROLE_HIERARCHY[$target_role->name] <= Constants::ROLE_HIERARCHY[$highest_current_role->name];
    }
}
