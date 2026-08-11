<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\EventRsvpFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A guest joining a competition/guest event (free RSVP — no payment).
 */
class EventRsvp extends Model
{
    /** @use HasFactory<EventRsvpFactory> */
    use HasFactory;

    protected $fillable = [
        'training_event_id',
        'name',
        'email',
        'phone',
        'guests',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'guests' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<TrainingEvent, $this>
     */
    public function trainingEvent(): BelongsTo
    {
        return $this->belongsTo(TrainingEvent::class);
    }
}
