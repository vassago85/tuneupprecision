<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingType extends Model
{
    /** @use HasFactory<\Database\Factories\TrainingTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'blurb',
        'learnings',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'learnings' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<CourseTemplate, $this>
     */
    public function courseTemplates(): HasMany
    {
        return $this->hasMany(CourseTemplate::class);
    }

    /**
     * @return HasMany<Testimonial, $this>
     */
    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    /**
     * @param  Builder<TrainingType>  $query
     */
    public function scopeActiveOrdered(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('name');
    }
}
