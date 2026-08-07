<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\BookingStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\TrainingEventStatus;
use App\Mail\PaymentConfirmation;
use App\Models\Booking;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * The single "money landed → confirm it" entry point for BOTH course bookings
 * and shop orders.
 *
 * Phase 1: an admin clicks "Mark as paid" in Filament.
 * Phase 2: a payment-gateway callback will call this exact same action.
 *
 * Nothing downstream should need to change between the two phases.
 */
class MarkPaid
{
    public function handle(Payment $payment): Payment
    {
        return DB::transaction(function () use ($payment): Payment {
            // Idempotent: a second call (e.g. gateway retry) is a no-op.
            if ($payment->status === PaymentStatus::Paid) {
                return $payment;
            }

            $payment->forceFill([
                'status' => PaymentStatus::Paid,
                'paid_at' => now(),
            ])->save();

            $payable = $payment->payable()->first();

            match (true) {
                $payable instanceof Order => $this->confirmOrder($payable),
                $payable instanceof Booking => $this->confirmBooking($payable),
                default => null,
            };

            if ($payable !== null && filled($payable->email)) {
                // Stubbed confirmation email — real templates land in a later commit.
                Mail::to($payable->email)->queue(new PaymentConfirmation($payable));
            }

            return $payment;
        });
    }

    /**
     * Money landed on a shop order: decrement stock per line, mark order paid.
     * Stock is decremented HERE (on payment), never on add-to-cart.
     */
    protected function confirmOrder(Order $order): void
    {
        if ($order->status === OrderStatus::Paid || $order->status === OrderStatus::Fulfilled) {
            return;
        }

        $order->loadMissing('orderItems.product');

        foreach ($order->orderItems as $item) {
            $item->product?->decrement('stock_qty', (int) $item->qty);
        }

        $order->update(['status' => OrderStatus::Paid]);
    }

    /**
     * Money landed on a booking: lock the seat (confirm) and clear the hold.
     *
     * Seats are reserved (seats_taken incremented) when the booking hold is
     * created in the booking flow — see the release-holds command which frees
     * them again on expiry. Confirming therefore does NOT re-increment; it just
     * makes the already-held seat permanent and drops the expiry.
     */
    protected function confirmBooking(Booking $booking): void
    {
        if ($booking->status === BookingStatus::Confirmed) {
            return;
        }

        $booking->update([
            'status' => BookingStatus::Confirmed,
            'hold_expires_at' => null,
        ]);

        $event = $booking->trainingEvent()->first();

        if ($event !== null && $event->isFull() && $event->status === TrainingEventStatus::Published) {
            $event->update(['status' => TrainingEventStatus::Full]);
        }
    }
}
