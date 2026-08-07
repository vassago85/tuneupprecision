<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TrainingType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TrainingType>
 */
class TrainingTypeFactory extends Factory
{
    protected $model = TrainingType::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'blurb' => fake()->sentence(),
            'icon' => null,
            'sort_order' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }
}
