<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a user with Admin role
     */
    public function admin(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('Admin');
        });
    }

    /**
     * Create a user with Manager role
     */
    public function manager(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('Manager');
        });
    }

    /**
     * Create a user with Coordinator role
     */
    public function coordinator(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('Coordinator');
        });
    }

    /**
     * Create a user with Viewer role
     */
    public function viewer(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('Viewer');
        });
    }
}
