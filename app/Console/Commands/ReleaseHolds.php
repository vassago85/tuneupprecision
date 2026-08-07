<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Enums\TrainingEventStatus;
use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReleaseHolds extends Command
{
    protected $signature = 'bookings:release-holds';

    protected $description = 'Cancel pending bookings whose hold has expired and free their reserved seats.';

    public function handle(): int
    {
        $expired = Booking::query()
            ->where('status', BookingStatus::Pending)
            ->whereNotNull('hold_expires_at')
            ->where('hold_expires_at', '<', now())
            ->with('trainingEvent')
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No expired holds to release.');

            return self::SUCCESS;
        }

        foreach ($expired as $booking) {
            DB::transaction(function () use ($booking): void {
                $event = $booking->trainingEvent;

                if ($event !== null) {
                    // Free the seats this hold had reserved.
                    $event->decrement('seats_taken', (int) $booking->seats);

                    // A previously "full" event may now have space again.
                    $event->refresh();
                    if ($event->status === TrainingEventStatus::Full && ! $event->isFull()) {
                        $event->update(['status' => TrainingEventStatus::Published]);
                    }
                }

                $booking->update(['status' => BookingStatus::Cancelled]);
            });
        }

        $this->info("Released {$expired->count()} expired booking hold(s).");

        return self::SUCCESS;
    }
}
