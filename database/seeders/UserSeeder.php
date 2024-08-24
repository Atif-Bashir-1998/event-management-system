<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = Role::all(); // Retrieve all roles

        // Create users and assign random roles
        User::factory()->count(10)->create()->each(function ($user) use ($roles) {
            // Assign a random role to the user
            $user->assignRole($roles->random()->name);
        });
    }
}
