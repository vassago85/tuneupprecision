<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\QuoteStatus;
use App\Enums\RiflePlatform;
use App\Models\Quote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quote>
 */
class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        $subtotal = fake()->numberBetween(100000, 5000000);
        $discount = 0;
        $total = $subtotal - $discount;
        $ex = (int) round($total / 1.15);

        return [
            'status' => QuoteStatus::Draft,
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'licence_status' => 'Dedicated sport shooter',
            'platform' => RiflePlatform::Barrelled,
            'chambering' => null,
            'barrel_length' => null,
            'barrel_twist' => null,
            'barrel_finish' => null,
            'subtotal_cents' => $subtotal,
            'discount_amount_cents' => $discount,
            'total_cents' => $total,
            'vat_amount_cents' => $total - $ex,
            'deposit_percent' => 50,
            'lead_time' => '8–12 weeks',
            'notes' => null,
            'valid_until' => now()->addDays(14)->toDateString(),
            'created_by' => null,
        ];
    }
}
