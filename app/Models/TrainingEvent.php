<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TrainingEventStatus;
use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingEvent extends Model
{
    /** @use HasFactory<\Database\Factories\TrainingEventFactory> */
    use HasFactory;

    protected $fillable = [
        'course_template_id',
        'starts_on',
        'ends_on',
        'venue',
        'capacity',
        'seats_taken',
        'price_cents',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'capacity' => 'integer',
            'seats_taken' => 'integer',
            'price_cents' => 'integer',
            'status' => TrainingEventStatus::class,
        ];
    }

    /**
     * @return BelongsTo<CourseTemplate, $this>
     */
    public function courseTemplate(): BelongsTo
    {
        return $this->belongsTo(CourseTemplate::class);
    }

    /**
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function seatsLeft(): int
    {
        return max(0, (int) $this->capacity - (int) $this->seats_taken);
    }

    public function isFull(): bool
    {
        return $this->seatsLeft() <= 0;
    }

    /**
     * Price in cents — event override wins, otherwise the template base price.
     */
    public function effectivePriceCents(): int
    {
        return (int) ($this->price_cents ?? $this->courseTemplate?->base_price_cents ?? 0);
    }

    /**
     * Display price, e.g. "R1 850.00".
     */
    protected function price(): Attribute
    {
        return Attribute::get(fn (): string => Money::format($this->effectivePriceCents()));
    }

    /**
     * Events shown on the public site: published or full (never draft/cancelled).
     *
     * @param  Builder<TrainingEvent>  $query
     */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->whereIn('status', array_map(
            fn (TrainingEventStatus $s): string => $s->value,
            TrainingEventStatus::publiclyVisible(),
        ));
    }

    /**
     * @param  Builder<TrainingEvent>  $query
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('starts_on', '>=', now()->toDateString())
            ->orderBy('starts_on');
    }
}
