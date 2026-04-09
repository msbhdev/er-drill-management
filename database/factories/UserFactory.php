<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Rig;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'role' => UserRole::STO->value,
            'rig_id' => Rig::factory(),
            'active_status' => true,
            'description' => fake()->sentence(),
            'password' => static::$password ??= Hash::make('password'),
            'last_login_at' => now(),
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'must_change_password' => false,
            'name_confirmed_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Mark the user as an administrator.
     */
    public function administrator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Administrator->value,
            'rig_id' => null,
        ]);
    }
}
