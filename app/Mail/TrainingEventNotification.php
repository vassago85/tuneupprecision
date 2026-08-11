<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\TrainingEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Ad-hoc notification sent by an admin to the people booked on a single
 * training event (schedule changes, kit reminders, venue notes, etc.).
 */
class TrainingEventNotification extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public TrainingEvent $event,
        public string $subjectLine,
        public string $body,
        public string $recipientName = '',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.training-event-notification',
            with: [
                'event' => $this->event,
                'body' => $this->body,
                'recipientName' => $this->recipientName,
            ],
        );
    }
}
