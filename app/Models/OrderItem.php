<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    /** @use HasFactory<\Database\Factories\OrderItemFactory> */
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'name_snapshot',
        'price_cents_snapshot',
        'qty',
    ];

    protected function casts(): array
    {
        return [
            'price_cents_snapshot' => 'integer',
            'qty' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function lineTotalCents(): int
    {
        return (int) $this->price_cents_snapshot * (int) $this->qty;
    }

    /**
     * Display line total, e.g. "R640.00".
     */
    protected function lineTotal(): Attribute
    {
        return Attribute::get(fn (): string => Money::format($this->lineTotalCents()));
    }
}
