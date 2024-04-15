<?php

namespace Database\Seeders;

use App\Models\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = ['Super Admin', ' Admin', 'Staff', 'Client'];
        $i = 1;
        $this->command->info('Creating user roles...');

        foreach ($roles as $role) {
            UserRole::create([
                'id' => $i,
                'role_name' => $role
            ]);

            $i++;
        }
        $this->command->info('User roles created.');
    }
}
