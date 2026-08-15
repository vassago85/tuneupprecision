<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Component;
use App\Models\ComponentCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Component>
 */
class ComponentFactory extends Factory
{
    protected $model = Component::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'component_category_id' => ComponentCategory::factory(),
            'brand' => fake()->company(),
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('###'),
            'specs' => [fake()->word(), fake()->word()],
            'price_cents' => fake()->numberBetween(10000, 5000000),
            'cost_cents' => fake()->numberBetween(5000, 4000000),
            'image_path' => null,
            'footprint' => null,
            'fits_footprints' => null,
            'tube_diameter' => null,
            'fits_tube_diameters' => null,
            'lead_time_weeks' => null,
            'is_active' => true,
            'is_automatic' => false,
            'allows_quantity' => false,
            'requires_aftermarket_trigger' => false,
            'is_factory_option' => false,
            'sort_order' => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    public function automatic(): static
    {
        return $this->state(fn (): array => ['is_automatic' => true]);
    }
}
