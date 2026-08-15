<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BuildEnquiry extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Quote $quote,
        public bool $forCustomer = false,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->forCustomer
            ? 'We received your rifle build request · '.$this->quote->reference
            : 'New rifle build enquiry · '.$this->quote->reference;

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.build-enquiry',
            with: [
                'quote' => $this->quote,
                'forCustomer' => $this->forCustomer,
            ],
        );
    }
}
