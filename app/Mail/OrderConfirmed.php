<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use App\Shop\OrderInvoice;
use App\Support\Money;
use App\Support\VatPrice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmed extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order confirmed · '.$this->order->reference,
        );
    }

    public function content(): Content
    {
        $this->order->loadMissing('orderItems');

        return new Content(
            markdown: 'emails.order-confirmed',
            with: [
                'order' => $this->order,
                'vatIncluded' => Money::format(VatPrice::includedVatCents($this->order->totalCents())),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $pdf = app(OrderInvoice::class)->pdf($this->order)->output();

        return [
            Attachment::fromData(fn (): string => $pdf, $this->order->reference.'-invoice.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
