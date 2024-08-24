<?php

namespace App\Http\Controllers\RolePermission;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view_role', only: ['index']),
            new Middleware('can:create_role', only: ['store']),
            new Middleware('can:update_role', only: ['update']),
            new Middleware('can:delete_role', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $roles = Role::all()->load('permissions');
        $permissions = Permission::all();

        return Inertia::render('RolePermission/Role', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:roles|max:255',
        ]);

        Role::create(['name' => $validated['name']]);

        return back()->with('success', 'Role has been created');
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|unique:roles|max:255',
        ]);

        $role->update([
            'name' => $validated['name'],
        ]);

        return back()->with('success', 'Role has been updated');
    }

    public function destroy(Request $request, Role $role)
    {
        $users_with_current_role = User::role($role->name)->get()->count();

        if ($users_with_current_role) {
            return back()->with('error', 'Can not delete role with existing users');
        }

        $role->delete();

        return back()->with('success', 'Role has been deleted');
    }
}
