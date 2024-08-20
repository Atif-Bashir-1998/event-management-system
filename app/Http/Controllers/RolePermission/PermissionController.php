<?php

namespace App\Http\Controllers\RolePermission;

use App\Constants\RolePermission\Constants;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('can:view_permission', only: ['index']),
            new Middleware('can:create_permission', only: ['store']),
            new Middleware('can:update_permission', only: ['update']),
            new Middleware('can:delete_permission', only: ['destroy']),
        ];
    }

    public function index()
    {
        $roles = Role::all()->load('permissions');
        $permissions = Permission::all();

        return Inertia::render('RolePermission/Permission', [
            'roles' => $roles,
            'permissions' => $permissions
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|unique:roles|max:255',
        ]);

        Role::create(['name' => $validated['name']]);

        return back()->with('success', Constants::PERMISSION_CREATE_SUCCESS);
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'required|unique:roles|max:255',
        ]);

        $permission->update([
            'name' => $validated['name']
        ]);

        return back()->with('success', Constants::PERMISSION_UPDATE_SUCCESS);
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return back()->with('success', Constants::ROLE_DELETE_SUCCESS);
    }
}
