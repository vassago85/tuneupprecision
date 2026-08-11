<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Money;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'price_cents',
        'stock_qty',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'stock_qty' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(400)
            ->height(400)
            ->optimize()
            ->nonQueued();

        // Compressed, web-sized image for detail views (never serve the original).
        $this->addMediaConversion('web')
            ->fit(Fit::Max, 1600, 1600)
            ->optimize()
            ->nonQueued();
    }

    /**
     * Out-of-stock or inactive products simply don't display — there is no
     * "sold out" state on the public site.
     *
     * @param  Builder<Product>  $query
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('stock_qty', '>', 0);
    }

    /**
     * Display price, e.g. "R320.00".
     */
    protected function price(): Attribute
    {
        return Attribute::get(fn (): string => Money::format((int) $this->price_cents));
    }
}
