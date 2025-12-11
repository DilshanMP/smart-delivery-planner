<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $companyTypes = ['Logistics', 'Transport', 'Delivery', 'Shipping', 'Express', 'Distribution'];
        $suffixes = ['Ltd', '(Pvt) Ltd', 'Inc', 'LLC', 'Co'];

        return [
            'code' => 'COMP' . str_pad(fake()->unique()->numberBetween(1, 999), 3, '0', STR_PAD_LEFT),
            'name' => fake()->company() . ' ' . fake()->randomElement($companyTypes) . ' ' . fake()->randomElement($suffixes),
            'address' => fake()->streetAddress() . ', ' . fake()->city() . ', Sri Lanka',
            'contact_person' => fake()->name(),
            'contact_number' => '+947' . fake()->numberBetween(70000000, 79999999),
            'email' => fake()->unique()->safeEmail(),
            'is_active' => fake()->boolean(90), // 90% active
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }
}
