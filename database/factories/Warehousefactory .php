<?php

namespace Database\Factories;

use App\Models\Warehouse;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        // Sri Lankan cities with approximate GPS coordinates
        $locations = [
            ['city' => 'Colombo', 'lat' => 6.9271, 'lon' => 79.8612],
            ['city' => 'Kandy', 'lat' => 7.2906, 'lon' => 80.6337],
            ['city' => 'Galle', 'lat' => 6.0535, 'lon' => 80.2210],
            ['city' => 'Jaffna', 'lat' => 9.6615, 'lon' => 80.0255],
            ['city' => 'Negombo', 'lat' => 7.2094, 'lon' => 79.8358],
            ['city' => 'Matara', 'lat' => 5.9549, 'lon' => 80.5550],
            ['city' => 'Kurunegala', 'lat' => 7.4818, 'lon' => 80.3609],
            ['city' => 'Anuradhapura', 'lat' => 8.3114, 'lon' => 80.4037],
        ];

        $location = fake()->randomElement($locations);

        return [
            'company_id' => Company::factory(),
            'code' => 'WH-' . strtoupper(substr($location['city'], 0, 3)) . '-' . fake()->unique()->numberBetween(100, 999),
            'name' => fake()->randomElement(['Main', 'Central', 'Regional', 'Distribution']) . ' Warehouse ' . $location['city'],
            'address' => fake()->streetAddress() . ', ' . $location['city'] . ', Sri Lanka',
            'latitude' => $location['lat'] + fake()->randomFloat(4, -0.05, 0.05),
            'longitude' => $location['lon'] + fake()->randomFloat(4, -0.05, 0.05),
            'is_active' => fake()->boolean(95), // 95% active
            'notes' => fake()->optional(0.2)->sentence(),
        ];
    }
}
