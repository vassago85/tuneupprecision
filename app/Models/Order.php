<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use App\Support\HasReference;
use App\Support\Money;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    use HasReference;

    /** Reference prefix => TU-S-000123 */
    protected string $referencePrefix = 'TU-S';

    protected $fillable = [
        'reference',
        'customer_name',
        'email',
        'phone',
        'address_line_1',
        'address_line_2',
        'suburb',
        'city',
        'province',
        'postal_code',
        'subtotal_cents',
        'shipping_cents',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_cents' => 'integer',
            'shipping_cents' => 'integer',
            'status' => OrderStatus::class,
        ];
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return MorphOne<Payment, $this>
     */
    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    /**
     * Display subtotal, e.g. "R1 850.00".
     */
    protected function subtotal(): Attribute
    {
        return Attribute::get(fn (): string => Money::format((int) $this->subtotal_cents));
    }

    public function totalCents(): int
    {
        return (int) $this->subtotal_cents + (int) $this->shipping_cents;
    }

    protected function total(): Attribute
    {
        return Attribute::get(fn (): string => Money::format($this->totalCents()));
    }

    public function deliveryAddress(): string
    {
        return collect([
            $this->address_line_1,
            $this->address_line_2,
            $this->suburb,
            trim(implode(' ', array_filter([(string) $this->city, (string) $this->postal_code]))),
            $this->province,
        ])->filter(fn (?string $line): bool => filled($line))->implode(', ');
    }
}
