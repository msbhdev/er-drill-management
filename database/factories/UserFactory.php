<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\AccountAppAccess;
use App\Models\Rig;
use App\Models\RoleAssigneeSchedule;
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
            'account_type' => 'shared_role',
            'role_code' => UserRole::STO->value,
            'rig_code' => fn () => Rig::factory()->create()->code,
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

    public function configure(): static
    {
        return $this->afterCreating(function (User $user): void {
            AccountAppAccess::query()->firstOrCreate(
                [
                    'account_id' => $user->id,
                    'app_code' => config('er_drill.auth_app_code'),
                ],
                ['is_active' => true]
            );

            if ($user->role && $user->rig_code) {
                RoleAssigneeSchedule::query()->firstOrCreate(
                    [
                        'account_id' => $user->id,
                        'effective_from' => now()->toDateString(),
                    ],
                    [
                        'person_name' => $user->full_name,
                        'role_code' => $user->role,
                        'rig_code' => $user->rig_code,
                        'effective_to' => null,
                        'remarks' => 'Seeded from user factory.',
                        'active_status' => true,
                    ]
                );
            }
        });
    }

    /**
     * Mark the user as an administrator.
     */
    public function administrator(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => 'admin',
            'role_code' => UserRole::Administrator->value,
            'rig_code' => null,
        ]);
    }
}
