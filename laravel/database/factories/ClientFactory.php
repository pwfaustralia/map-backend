<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'preferred_name' => fake()->name(),
            'middle_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'home_phone' => fake()->phoneNumber(),
            'work_phone' => fake()->phoneNumber(),
            'mobile_phone' => fake()->phoneNumber(),
            'fax' => fake()->phoneNumber(),
            'address_1' => fake()->streetAddress(),
            'address_2' => "",
            'city' => fake()->city(),
            'state' => fake()->state(),
            'country' => fake()->country(),
            'postcode' => fake()->postcode(),
        ];
    }
}
