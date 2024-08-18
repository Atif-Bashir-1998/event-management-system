<?php

namespace App\Http\Controllers\RolePermission;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use App\Constants\RolePermission\Constants;

class RoleController extends Controller
{
    public function index()
    {
        // return response(null, 200);
        $roles = Role::all();
        return Inertia::render('RolePermission/Role', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $role_name = $request->input('name');

        Role::create(['name' => $role_name]);

        return back()->with('success', Constants::ROLE_CREATE_SUCCESS);
    }

    public function update(Request $request, Role $role)
    {
        $role->update([
            'name' => $request->input('name')
        ]);

        return back()->with('success', Constants::ROLE_UPDATE_SUCCESS);
    }

    public function destroy(Role $role)
    {
        $users_with_current_role = User::role($role->name)->get()->count();

        if ($users_with_current_role) {
            return back()->with('error', Constants::ROLE_DELETE_ERROR);
        }

        $role->delete();

        return back()->with('success', Constants::ROLE_DELETE_SUCCESS);
    }
}
