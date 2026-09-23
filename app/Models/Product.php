<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Money;
use App\Support\VatPrice;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
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
        'sku',
        'category',
        'description',
        'cost_cents',
        'selling_ex_vat_cents',
        'round_price_up',
        'price_cents',
        'stock_qty',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cost_cents' => 'integer',
            'selling_ex_vat_cents' => 'integer',
            'round_price_up' => 'boolean',
            'price_cents' => 'integer',
            'stock_qty' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            if ($product->isDirty('name') && ! $product->isDirty('slug')) {
                $product->slug = static::uniqueSlug((string) $product->name, $product->id);
            }

            if ($product->isDirty('description') && blank($product->description)) {
                $product->description = null;
            }

            if ($product->selling_ex_vat_cents === null) {
                return;
            }

            $product->price_cents = VatPrice::inclusiveCents(
                (int) $product->selling_ex_vat_cents,
                (bool) $product->round_price_up,
            );
        });
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
        return $query
            ->where('is_active', true)
            ->where('stock_qty', '>', 0)
            ->where('price_cents', '>', 0);
    }

    /**
     * Shop URL slug from a customer-facing name. Ignores $ignoreId so a rename
     * does not collide with the product's own current slug.
     */
    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'product';
        }

        $slug = $base;
        $suffix = 2;

        while (static::query()
            ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * Rand amount edited in the products table. Stored as selling_ex_vat_cents.
     */
    protected function sellingExVat(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->selling_ex_vat_cents === null
                ? null
                : number_format($this->selling_ex_vat_cents / 100, 2, '.', ''),
            set: function (mixed $value): array {
                if ($value === null || $value === '') {
                    return ['selling_ex_vat_cents' => null];
                }

                return ['selling_ex_vat_cents' => Money::toCents($value)];
            },
        );
    }

    /**
     * Display price, e.g. "R320.00".
     */
    protected function price(): Attribute
    {
        return Attribute::get(fn (): string => Money::format((int) $this->price_cents));
    }
}
