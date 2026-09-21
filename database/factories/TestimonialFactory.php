<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TestimonialSource;
use App\Models\Testimonial;
use App\Models\TrainingType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    public function definition(): array
    {
        return [
            'training_type_id' => TrainingType::factory(),
            'training_event_id' => null,
            'author_name' => fake()->name(),
            'body' => fake()->paragraph(2),
            'is_approved' => false,
            'source' => TestimonialSource::Manual,
            'submitted_at' => now(),
            'approved_at' => null,
        ];
    }

    public function approved(): self
    {
        return $this->state(fn (): array => [
            'is_approved' => true,
            'approved_at' => now(),
        ]);
    }

    public function fromShooter(): self
    {
        return $this->state(fn (): array => [
            'source' => TestimonialSource::Shooter,
        ]);
    }
}
