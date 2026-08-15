<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ComponentSelectionMode;
use Database\Factories\ComponentCategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComponentCategory extends Model
{
    /** @use HasFactory<ComponentCategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'hint',
        'selection_mode',
        'is_optional',
        'allows_quantity',
        'is_hidden',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'selection_mode' => ComponentSelectionMode::class,
            'is_optional' => 'boolean',
            'allows_quantity' => 'boolean',
            'is_hidden' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<Component, $this>
     */
    public function components(): HasMany
    {
        return $this->hasMany(Component::class)->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Categories that appear as builder steps (not hidden labour).
     *
     * @param  Builder<ComponentCategory>  $query
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_hidden', false)->orderBy('sort_order')->orderBy('name');
    }
}
