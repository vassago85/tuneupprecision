<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\TrainingEventStatus;
use App\Support\HasReference;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
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
        'testimonial_invited_at',
    ];

    protected function casts(): array
    {
        return [
            'seats' => 'integer',
            'amount_cents' => 'integer',
            'status' => BookingStatus::class,
            'hold_expires_at' => 'datetime',
            'testimonial_invited_at' => 'datetime',
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
     * A confirmed booking whose event is over: marked Completed, or its last
     * day (ends_on, else starts_on) is before today. Not a stored status, so
     * testimonial invites and attendee mail still see these as Confirmed.
     */
    public function isCompleted(): bool
    {
        $event = $this->trainingEvent;

        if ($this->status !== BookingStatus::Confirmed || $event === null) {
            return false;
        }

        if ($event->status === TrainingEventStatus::Completed) {
            return true;
        }

        $lastDay = $event->ends_on ?? $event->starts_on;

        return $lastDay !== null && $lastDay->lt(today());
    }

    /**
     * @param  Builder<Booking>  $query
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', BookingStatus::Confirmed)
            ->whereHas('trainingEvent', fn (Builder $event): Builder => $event->where(self::eventIsOver(...)));
    }

    /**
     * Confirmed bookings whose event is still to come (or running today).
     *
     * @param  Builder<Booking>  $query
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('status', BookingStatus::Confirmed)
            ->whereDoesntHave('trainingEvent', fn (Builder $event): Builder => $event->where(self::eventIsOver(...)));
    }

    /**
     * @param  Builder<TrainingEvent>  $event
     */
    private static function eventIsOver(Builder $event): Builder
    {
        return $event->where('status', TrainingEventStatus::Completed)
            ->orWhereRaw('coalesce(ends_on, starts_on) < ?', [today()->toDateString()]);
    }

    /**
     * Display amount, e.g. "R1 850.00".
     */
    protected function amount(): Attribute
    {
        return Attribute::get(fn (): string => Money::format((int) $this->amount_cents));
    }
}
