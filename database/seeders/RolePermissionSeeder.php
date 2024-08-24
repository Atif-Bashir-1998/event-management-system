<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // creating permissions
        $resourceful_features = [
            'role',
            'permission',
            'user'
        ];

        foreach ($resourceful_features as $feature) {
            $operations = ['create', 'update', 'view', 'delete'];

            foreach ($operations as $operation) {
                Permission::create(['name' => $operation . "_" . $feature]);
            }
        }

        // additional permissions
        $additional_permissions = [
            'view_access_control',
            'add_user_permission',
            'remove_user_permission',
            'add_role_permission',
            'remove_role_permission'
        ];

        foreach ($additional_permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // creating basic roles
        $admin_role = Role::create(['name' => 'admin']);
        Role::create(['name' => 'organizer']);
        Role::create(['name' => 'attendee']);

        $admin_role->syncPermissions(Permission::all());
    }
}
