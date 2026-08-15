<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Booking;
use App\Models\Order;
use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Stub confirmation email for a paid booking OR order.
 *
 * TODO: Real branded email templates (and copy) land in a later commit.
 */
class PaymentConfirmation extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Model $payable) {}

    public function envelope(): Envelope
    {
        $reference = $this->payable->reference ?? '';

        $subject = match (true) {
            $this->payable instanceof Booking => "Booking confirmed · {$reference}",
            $this->payable instanceof Order => "Order confirmed · {$reference}",
            $this->payable instanceof Quote => "Deposit received · {$reference}",
            default => 'Payment confirmed',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payment-confirmation',
            with: [
                'payable' => $this->payable,
                'isBooking' => $this->payable instanceof Booking,
                'isOrder' => $this->payable instanceof Order,
                'isQuote' => $this->payable instanceof Quote,
            ],
        );
    }
}
