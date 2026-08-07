<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Actions\MarkPaid;
use App\Enums\BookingStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Order;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

/**
 * A single "Mark as paid" row action, usable on Bookings, Orders and Payments.
 *
 * All three route through the one MarkPaid action — exactly the same entry
 * point a payment-gateway callback will use in Phase 2.
 */
class MarkAsPaidAction
{
    public static function make(): Action
    {
        return Action::make('markAsPaid')
            ->label('Mark as paid')
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Confirm payment received')
            ->modalDescription('This confirms the booking / decrements stock and sends a confirmation email. Use once the EFT reflects.')
            ->action(function (Model $record): void {
                $payment = static::resolvePayment($record);

                app(MarkPaid::class)->handle($payment);

                Notification::make()
                    ->title('Marked as paid')
                    ->success()
                    ->send();
            })
            ->visible(fn (Model $record): bool => static::isPayable($record) && ! static::alreadyPaid($record));
    }

    protected static function isPayable(Model $record): bool
    {
        return $record instanceof Payment
            || $record instanceof Booking
            || $record instanceof Order;
    }

    protected static function alreadyPaid(Model $record): bool
    {
        return match (true) {
            $record instanceof Payment => $record->status === PaymentStatus::Paid,
            $record instanceof Booking => $record->status === BookingStatus::Confirmed,
            $record instanceof Order => in_array($record->status, [OrderStatus::Paid, OrderStatus::Fulfilled], true),
            default => true,
        };
    }

    protected static function resolvePayment(Model $record): Payment
    {
        if ($record instanceof Payment) {
            return $record;
        }

        // Booking or Order: create the EFT payment shell on first confirmation.
        $amountCents = $record instanceof Booking
            ? (int) $record->amount_cents
            : (int) $record->subtotal_cents;

        return $record->payment()->firstOrCreate([], [
            'method' => PaymentMethod::Eft->value,
            'amount_cents' => $amountCents,
            'status' => PaymentStatus::Pending->value,
            'reference' => $record->reference,
        ]);
    }
}
