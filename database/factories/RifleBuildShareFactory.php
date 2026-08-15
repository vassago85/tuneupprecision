<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RifleBuildShare;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RifleBuildShare>
 */
class RifleBuildShareFactory extends Factory
{
    protected $model = RifleBuildShare::class;

    public function definition(): array
    {
        return [
            'payload' => [
                'platform' => 'barrelled',
                'singles' => [],
                'multis' => [],
                'quantities' => [],
                'chambering' => '6.5 Creedmoor',
                'barrel_length' => '26"',
                'barrel_twist' => '1:8',
                'barrel_finish' => 'Bead-blast stainless',
                'discount_percent' => 0,
                'discount_amount_cents' => 0,
                'deposit_percent' => 50,
            ],
        ];
    }
}
