<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Enums\TrainingEventStatus;
use App\Models\Booking;
use App\Models\TrainingEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Reserve seats on a published training date.
 *
 * seats_taken goes up here, while the booking is still pending. Confirming
 * payment (MarkPaid) does not add the seats again. bookings:release-holds
 * frees them if the hold expires unpaid.
 */
final class PlaceBooking
{
    /**
     * @param  array{customer_name: string, email: string, phone: string, rifle: ?string, seats: int}  $details
     */
    public function place(TrainingEvent $event, array $details): Booking
    {
        return DB::transaction(function () use ($event, $details): Booking {
            $locked = TrainingEvent::query()->whereKey($event->id)->lockForUpdate()->firstOrFail();

            if (! $this->isBookable($locked)) {
                throw ValidationException::withMessages([
                    'seats' => 'That date is no longer open for booking.',
                ]);
            }

            $seats = (int) $details['seats'];

            if ($seats < 1 || $seats > $locked->seatsLeft()) {
                throw ValidationException::withMessages([
                    'seats' => 'Only '.$locked->seatsLeft().' seat(s) left on that date.',
                ]);
            }

            $locked->seats_taken = (int) $locked->seats_taken + $seats;

            if ($locked->isFull() && $locked->status === TrainingEventStatus::Published) {
                $locked->status = TrainingEventStatus::Full;
            }

            $locked->save();

            return Booking::query()->create([
                'training_event_id' => $locked->id,
                'customer_name' => $details['customer_name'],
                'email' => $details['email'],
                'phone' => $details['phone'],
                'rifle' => $details['rifle'],
                'seats' => $seats,
                'amount_cents' => $locked->effectivePriceCents() * $seats,
                'status' => BookingStatus::Pending,
                'hold_expires_at' => now()->addHours($this->holdHours()),
            ]);
        });
    }

    public function isBookable(TrainingEvent $event): bool
    {
        if ($event->isCompetition() || $event->isFull() || ! $event->isOnActiveCourse()) {
            return false;
        }

        if (! in_array($event->status, TrainingEventStatus::publiclyVisible(), true)) {
            return false;
        }

        return $event->starts_on !== null && $event->starts_on->gte(now()->startOfDay());
    }

    public function holdHours(): int
    {
        return max(1, (int) config('tuneup.booking_hold_hours', 72));
    }
}
