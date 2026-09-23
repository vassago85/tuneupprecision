<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Testimonial;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Private copy of a submitted testimonial. One goes to the shooter, one to
 * Dirk. Neither version is the public site — the words stay hidden until
 * they are approved in the admin.
 */
class TestimonialCopy extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Testimonial $testimonial,
        public bool $forVisitor = false,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->forVisitor
            ? 'We received your testimonial · Tune Up Precision'
            : 'New testimonial from '.$this->testimonial->author_name;

        return new Envelope(
            subject: $subject,
            replyTo: $this->forVisitor || blank($this->testimonial->author_email)
                ? []
                : [new Address($this->testimonial->author_email, $this->testimonial->author_name)],
        );
    }

    public function content(): Content
    {
        $this->testimonial->loadMissing('trainingType');

        return new Content(
            markdown: 'emails.testimonial-copy',
            with: [
                'testimonial' => $this->testimonial,
                'forVisitor' => $this->forVisitor,
            ],
        );
    }
}
