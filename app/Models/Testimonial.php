<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TestimonialSource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    /** @use HasFactory<\Database\Factories\TestimonialFactory> */
    use HasFactory;

    protected $fillable = [
        'training_type_id',
        'training_event_id',
        'author_name',
        'author_email',
        'body',
        'is_approved',
        'source',
        'submitted_at',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'source' => TestimonialSource::class,
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<TrainingType, $this>
     */
    public function trainingType(): BelongsTo
    {
        return $this->belongsTo(TrainingType::class);
    }

    /**
     * @return BelongsTo<TrainingEvent, $this>
     */
    public function trainingEvent(): BelongsTo
    {
        return $this->belongsTo(TrainingEvent::class);
    }

    /**
     * Approved testimonials only — the ones that show on the public site.
     *
     * @param  Builder<Testimonial>  $query
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    /**
     * Newest first — falls back to created_at for testimonials that were
     * approved but never had `approved_at` set (shouldn't happen, but safe).
     *
     * @param  Builder<Testimonial>  $query
     */
    public function scopeLatestFirst(Builder $query): Builder
    {
        return $query
            ->orderByDesc('approved_at')
            ->orderByDesc('created_at');
    }

    /**
     * Human eyebrow line for the public carousel: discipline, optionally with
     * the event date when the testimonial is tied to a specific event.
     */
    public function displayEventLabel(): string
    {
        $type = $this->trainingType?->name ?? '';
        $date = $this->trainingEvent?->starts_on?->format('j M Y');

        return $date ? trim($type.' · '.$date) : $type;
    }
}
