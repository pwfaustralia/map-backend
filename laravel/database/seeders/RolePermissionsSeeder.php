<?php

namespace Database\Seeders;

use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class RolePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RolePermission::truncate();

        $csvFile = fopen(base_path("database/data/role_permissions.csv"), "r");
        $this->command->info('Creating role permissions...');
        $firstline = true;
        while (($data = fgetcsv($csvFile, 2000, ",")) !== FALSE) {
            if (!$firstline) {
                RolePermission::create([
                    "user_role_id" => $data['0'],
                    "scope_name" => $data['1'],
                    "scope_description" => $data['2']
                ]);
            }
            $firstline = false;
        }
        $this->command->info('Role permissions created.');
        fclose($csvFile);
    }
}
