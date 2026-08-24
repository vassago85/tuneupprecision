<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactEnquiry extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  array{name: string, email: string, phone: ?string, subject: string, message: string}  $enquiry
     */
    public function __construct(
        public array $enquiry,
        public bool $forVisitor = false,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->forVisitor
            ? 'We received your message · Tune Up Precision'
            : 'Website enquiry · '.$this->enquiry['subject'];

        return new Envelope(
            subject: $subject,
            replyTo: $this->forVisitor
                ? []
                : [new Address($this->enquiry['email'], $this->enquiry['name'])],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.contact-enquiry',
            with: [
                'enquiry' => $this->enquiry,
                'forVisitor' => $this->forVisitor,
            ],
        );
    }
}
