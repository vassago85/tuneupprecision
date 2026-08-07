<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BookingStatus;
use App\Support\HasReference;
use App\Support\Money;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;
    use HasReference;

    /** Reference prefix => TU-B-000123 */
    protected string $referencePrefix = 'TU-B';

    protected $fillable = [
        'training_event_id',
        'customer_name',
        'email',
        'phone',
        'rifle',
        'seats',
        'reference',
        'amount_cents',
        'status',
        'hold_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'seats' => 'integer',
            'amount_cents' => 'integer',
            'status' => BookingStatus::class,
            'hold_expires_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<TrainingEvent, $this>
     */
    public function trainingEvent(): BelongsTo
    {
        return $this->belongsTo(TrainingEvent::class);
    }

    /**
     * @return MorphOne<Payment, $this>
     */
    public function payment(): MorphOne
    {
        return $this->morphOne(Payment::class, 'payable');
    }

    /**
     * Display amount, e.g. "R1 850.00".
     */
    protected function amount(): Attribute
    {
        return Attribute::get(fn (): string => Money::format((int) $this->amount_cents));
    }
}
