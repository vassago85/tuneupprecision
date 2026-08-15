<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Money;
use Database\Factories\ComponentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Component extends Model
{
    /** @use HasFactory<ComponentFactory> */
    use HasFactory;

    protected $fillable = [
        'component_category_id',
        'brand',
        'name',
        'slug',
        'specs',
        'price_cents',
        'cost_cents',
        'image_path',
        'footprint',
        'fits_footprints',
        'tube_diameter',
        'fits_tube_diameters',
        'lead_time_weeks',
        'is_active',
        'is_automatic',
        'allows_quantity',
        'requires_aftermarket_trigger',
        'is_factory_option',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'specs' => 'array',
            'price_cents' => 'integer',
            'cost_cents' => 'integer',
            'fits_footprints' => 'array',
            'fits_tube_diameters' => 'array',
            'lead_time_weeks' => 'integer',
            'is_active' => 'boolean',
            'is_automatic' => 'boolean',
            'allows_quantity' => 'boolean',
            'requires_aftermarket_trigger' => 'boolean',
            'is_factory_option' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<ComponentCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ComponentCategory::class, 'component_category_id');
    }

    /**
     * Active, manually pickable catalogue rows. Inactive and automatic labour
     * lines are omitted from the public picker — same rule as the shop.
     *
     * @param  Builder<Component>  $query
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('is_automatic', false);
    }

    /**
     * Display price, e.g. "R24 500.00".
     */
    protected function price(): Attribute
    {
        return Attribute::get(fn (): string => Money::format((int) $this->price_cents));
    }

    /**
     * Gross-profit percent on the incl-VAT retail price. Zero-price items
     * (customer-supplied / no-charge) report 0.
     */
    public function grossProfitPercent(): float
    {
        $price = (int) $this->price_cents;

        if ($price <= 0) {
            return 0.0;
        }

        return (($price - (int) $this->cost_cents) / $price) * 100;
    }
}
