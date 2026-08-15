<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ComponentSelectionMode;
use App\Models\ComponentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ComponentCategory>
 */
class ComponentCategoryFactory extends Factory
{
    protected $model = ComponentCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'key' => Str::slug($name),
            'name' => Str::title($name),
            'hint' => fake()->sentence(),
            'selection_mode' => ComponentSelectionMode::Single,
            'is_optional' => false,
            'allows_quantity' => false,
            'is_hidden' => false,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn (): array => ['is_hidden' => true]);
    }

    public function multi(): static
    {
        return $this->state(fn (): array => [
            'selection_mode' => ComponentSelectionMode::Multi,
        ]);
    }
}
