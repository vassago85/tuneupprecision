<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\QuoteStatus;
use App\Enums\RiflePlatform;
use App\Support\Money;
use Database\Factories\QuoteFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Quote extends Model
{
    /** @use HasFactory<QuoteFactory> */
    use HasFactory;

    protected $fillable = [
        'reference',
        'status',
        'customer_name',
        'customer_email',
        'customer_phone',
        'licence_status',
        'platform',
        'chambering',
        'barrel_length',
        'barrel_twist',
        'barrel_finish',
        'subtotal_cents',
        'discount_amount_cents',
        'total_cents',
        'vat_amount_cents',
        'deposit_percent',
        'lead_time',
        'notes',
        'valid_until',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => QuoteStatus::class,
            'platform' => RiflePlatform::class,
            'subtotal_cents' => 'integer',
            'discount_amount_cents' => 'integer',
            'total_cents' => 'integer',
            'vat_amount_cents' => 'integer',
            'deposit_percent' => 'integer',
            'valid_until' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Quote $quote): void {
            if (blank($quote->reference)) {
                $quote->reference = static::generateReference();
            }

            if ($quote->valid_until === null) {
                $days = (int) config('tuneup.rifle_builder.quote_validity_days', 14);
                $quote->valid_until = now()->addDays($days)->toDateString();
            }
        });
    }

    /**
     * TU-{yy}{mm}-{4 digits}, unique.
     */
    public static function generateReference(): string
    {
        $prefix = 'TU-'.now()->format('ym').'-';

        do {
            $candidate = $prefix.str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (static::query()->where('reference', $candidate)->exists());

        return $candidate;
    }

    /**
     * @return HasMany<QuoteLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(QuoteLine::class)->orderBy('sort_order');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return MorphOne<Payment, $this>
     */
    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    /**
     * Alias so MarkPaid / PaymentConfirmation can read $payable->email.
     */
    protected function email(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->customer_email);
    }

    /**
     * Deposit amount in cents, derived from the discounted total.
     */
    public function depositCents(): int
    {
        return (int) round(((int) $this->total_cents) * ((int) $this->deposit_percent) / 100);
    }

    /**
     * Days remaining until valid_until. Negative once expired.
     */
    public function ageingDays(): ?int
    {
        if ($this->valid_until === null) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->valid_until, false);
    }

    protected function total(): Attribute
    {
        return Attribute::get(fn (): string => Money::format((int) $this->total_cents));
    }
}
