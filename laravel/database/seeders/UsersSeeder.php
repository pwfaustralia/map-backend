<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating super admin user...');
        User::create(['email' => 'hello@pwf.com.au', 'password' => bcrypt('LK^3gxs8!!8&hu'), 'user_role_id' => 1, 'name' => 'PWF Australia']);
        $this->command->info('Super admin user created.');

        $this->command->info('Creating fake client users...');
        // Create fake client users
        User::factory()->count(10)->create()->each(function ($user) {
            $physical_address = Address::factory()->create();
            $postal_address = Address::factory()->create();
            $client = Client::factory()->state([
                "user_id" => $user->id,
                "physical_address_id" => $physical_address->id,
                "postal_address_id" => $postal_address->id
            ])->create();
            $this->command->info("Client user $user->email created. Client id $client->id, user id $user->id.");
        });
    }
}
