<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Booking;
use App\Support\Eft;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingPlaced extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Booking $booking,
        public bool $forCustomer = true,
    ) {}

    public function envelope(): Envelope
    {
        $reference = $this->booking->reference;

        return new Envelope(
            subject: $this->forCustomer
                ? "Seat held · {$reference}"
                : "New course booking · {$reference}",
            replyTo: $this->forCustomer
                ? []
                : [new Address($this->booking->email, $this->booking->customer_name)],
        );
    }

    public function content(): Content
    {
        $this->booking->loadMissing('trainingEvent.courseTemplate');

        return new Content(
            markdown: 'emails.booking-placed',
            with: [
                'booking' => $this->booking,
                'event' => $this->booking->trainingEvent,
                'forCustomer' => $this->forCustomer,
                'eft' => Eft::details(),
            ],
        );
    }
}
