<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Support\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory;

    protected $fillable = [
        'payable_type',
        'payable_id',
        'method',
        'reference',
        'amount_cents',
        'status',
        'paid_at',
        'proof_path',
        'gateway_ref',
        'gateway_payload',
    ];

    protected function casts(): array
    {
        return [
            'method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'amount_cents' => 'integer',
            'paid_at' => 'datetime',
            'gateway_payload' => 'array',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Display amount, e.g. "R1 850.00".
     */
    protected function amount(): Attribute
    {
        return Attribute::get(fn (): string => Money::format((int) $this->amount_cents));
    }
}
