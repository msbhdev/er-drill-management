<?php

namespace Database\Factories;

use App\Models\Rig;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rig>
 */
class RigFactory extends Factory
{
    protected $model = Rig::class;

    public function definition(): array
    {
        $code = strtoupper(fake()->unique()->lexify('R??'));

        return [
            'name' => 'Rig '.$code,
            'code' => $code,
            'location' => fake()->city(),
            'is_active' => true,
        ];
    }
}
