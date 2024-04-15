<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'building' => fake()->buildingNumber(),
            'floor_unit_no' => fake()->buildingNumber(),
            'street_name' => fake()->streetAddress(),
            'town' => fake()->city(),
            'state_county' => fake()->state(),
            'country' => fake()->country(),
        ];
    }
}
