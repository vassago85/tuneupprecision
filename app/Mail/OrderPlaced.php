<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use App\Support\Eft;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlaced extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Order $order,
        public bool $forCustomer = true,
    ) {}

    public function envelope(): Envelope
    {
        $reference = $this->order->reference;

        return new Envelope(
            subject: $this->forCustomer
                ? "Order received · {$reference}"
                : "New shop order · {$reference}",
            replyTo: $this->forCustomer
                ? []
                : [new Address($this->order->email, $this->order->customer_name)],
        );
    }

    public function content(): Content
    {
        $this->order->loadMissing('orderItems');

        return new Content(
            markdown: 'emails.order-placed',
            with: [
                'order' => $this->order,
                'forCustomer' => $this->forCustomer,
                'eft' => Eft::details(),
            ],
        );
    }
}
