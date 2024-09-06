<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!User::where('email', '=', 'hello@pwf.com.au')->exists()) {
            $this->command->info('Creating super admin user...');
            User::create(['email' => 'hello@pwf.com.au', 'password' => bcrypt('LK^3gxs8!!8&hu'), 'user_role_id' => 1, 'name' => 'PWF Australia']);
            $this->command->info('Super admin user created.');
        }

        $this->command->info('Creating fake client users...');
        // Create fake client users
        User::factory()->count(20000)->create()->each(function ($user) {
            $client = Client::factory()->state([
                "user_id" => $user->id
            ])->create();
            $this->command->info("Client user $user->email created. Client id $client->id, user id $user->id.");
        });
    }
}
