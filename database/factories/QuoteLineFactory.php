<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Quote;
use App\Models\QuoteLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuoteLine>
 */
class QuoteLineFactory extends Factory
{
    protected $model = QuoteLine::class;

    public function definition(): array
    {
        $unit = fake()->numberBetween(10000, 2000000);

        return [
            'quote_id' => Quote::factory(),
            'component_id' => null,
            'group_label' => 'Chassis / Stock',
            'brand' => fake()->company(),
            'description' => fake()->words(3, true),
            'specs' => [fake()->word()],
            'quantity' => 1,
            'unit_price_cents' => $unit,
            'line_total_cents' => $unit,
            'unit_cost_cents' => (int) round($unit * 0.8),
            'sort_order' => 0,
        ];
    }
}
