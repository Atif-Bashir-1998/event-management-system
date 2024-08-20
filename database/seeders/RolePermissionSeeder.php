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
            'permission'
        ];

        foreach ($resourceful_features as $feature) {
            $operations = ['create', 'update', 'view', 'delete'];

            foreach ($operations as $operation) {
                Permission::create(['name' => $operation . "_" . $feature]);
            }
        }

        // creating basic roles
        $admin_role = Role::create(['name' => 'admin']);
        Role::create(['name' => 'organizer']);
        Role::create(['name' => 'attendee']);

        $admin_role->syncPermissions(Permission::all());
    }
}
