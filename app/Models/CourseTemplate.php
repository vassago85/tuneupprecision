<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseTemplate extends Model
{
    /** @use HasFactory<\Database\Factories\CourseTemplateFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'level',
        'blurb',
        'specs',
        'base_price_cents',
        'default_capacity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'specs' => 'array',
            'base_price_cents' => 'integer',
            'default_capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<TrainingEvent, $this>
     */
    public function trainingEvents(): HasMany
    {
        return $this->hasMany(TrainingEvent::class);
    }

    /**
     * Display price, e.g. "R1 850.00".
     */
    protected function basePrice(): Attribute
    {
        return Attribute::get(fn (): string => Money::format((int) $this->base_price_cents));
    }
}
