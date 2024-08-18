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
        $available_permissions = [
            'create_role',
            'update_role',
            'view_role',
            'delete_role',
        ];

        foreach ($available_permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // creating basic roles
        $admin_role = Role::create(['name' => 'admin']);
        Role::create(['name' => 'organizer']);
        Role::create(['name' => 'attendee']);

        $admin_role->syncPermissions(Permission::all());
    }
}
