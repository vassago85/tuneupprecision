<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The monthly newsletter, sent to one subscriber. Carries the subscriber so the
 * template can render a personal one-click unsubscribe link (CAN-SPAM / POPIA).
 */
class Newsletter extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public NewsletterSubscriber $subscriber,
        public string $subjectLine,
        public string $body,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.newsletter',
            with: [
                'body' => $this->body,
                'unsubscribeUrl' => $this->subscriber->unsubscribeUrl(),
                'recipientName' => (string) ($this->subscriber->name ?? ''),
            ],
        );
    }
}
