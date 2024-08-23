<?php

namespace App\Http\Controllers;

use App\Constants\RolePermission\Constants;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
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
        $users = User::all();

        return Inertia::render('User/Index', [
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|unique:users',
            'is_email_verified' => 'boolean',
            'password' => 'required|string',
            'role' => 'sometimes|string'
        ]);

        // check the role of current user, superior role than current role can not be created
        $new_user_role = Role::findByName($validated['role']);
        if(!$this->has_sufficient_role_level($request->user(), $new_user_role))
        {
            return back()->with('error', 'You do not have permission to assign a role superior to your own.');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'email_verified_at' => $validated['is_email_verified'] ? now() : null,
            'password' => Hash::make($validated['password'])
        ]);

        $user->assignRole($validated['role']);

        return back()->with('success', 'User created successfully');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|unique:users',
            'is_email_verified' => 'boolean',
            'password' => 'required|string',
            'role' => 'sometimes|string'
        ]);

        // check the role of current user, superior role can not be alloted
        $updated_user_role = Role::findByName($validated['role']);
        if(!$this->has_sufficient_role_level($request->user(), $updated_user_role))
        {
            return back()->with('error', 'You do not have permission to assign a role superior to your own.');
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'email_verified_at' => $validated['is_email_verified'] ? now() : null,
            'password' => Hash::make($validated['password'])
        ]);

        $user->syncRoles([$validated['role']]);

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
