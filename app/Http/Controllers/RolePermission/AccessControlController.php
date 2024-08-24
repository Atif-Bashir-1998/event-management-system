<?php

namespace App\Http\Controllers\RolePermission;

use App\Constants\RolePermission\Constants;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AccessControlController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:view_access_control', only: ['index']),
            new Middleware('can:add_user_permission', only: ['add_permission_to_user']),
            new Middleware('can:remove_user_permission', only: ['remove_permission_from_user']),
            new Middleware('can:add_role_permission', only: ['add_permission_to_role']),
            new Middleware('can:remove_role_permission', only: ['remove_permission_from_role']),
        ];
    }

    public function index()
    {
        $roles = Role::all()->load('permissions');
        $permissions = Permission::all();

        return Inertia::render('RolePermission/AccessControl', [
            'roles' => $roles,
            'permissions' => $permissions
        ]);
    }

    /**
     * Add a permission to a role.
    */
    public function add_permission_to_role(Request $request, Role $role)
    {
        $permission = Permission::findByName($request->input('permission'));

        if ($role->hasPermissionTo($permission)) {
            return back()->with('error', 'Role already has this permission');
        }

        $role->givePermissionTo($permission);

        return back()->with('success', 'Permission added to role successfully');
    }

    /**
     * Remove a permission from a role.
    */
    public function remove_permission_from_role(Request $request, Role $role)
    {
        $permission = Permission::findByName($request->input('permission'));

        if (!$role->hasPermissionTo($permission)) {
            return back()->with('error', 'Role does not have this permission');
        }

        $role->revokePermissionTo($permission);

        return back()->with('success', 'Permission removed from role successfully');
    }

    /**
     * Add a permission to a user.
    */
    public function add_permission_to_user(Request $request, User $user)
    {
        $permission = Permission::findByName($request->input('permission'));

        if ($user->hasPermissionTo($permission)) {
            return back()->with('error', 'User already has this permission');
        }

        $user->givePermissionTo($permission);

        return back()->with('success', 'Permission added to user successfully');
    }

    /**
     * Remove a permission from a user.
    */
    public function remove_permission_from_user(Request $request, User $user)
    {
        $permission = Permission::findByName($request->input('permission'));

        if (!$user->hasPermissionTo($permission)) {
            return back()->with('error', 'User does not have this permission');
        }

        $user->revokePermissionTo($permission);

        return back()->with('success', 'Permission removed from user successfully');
    }
}
