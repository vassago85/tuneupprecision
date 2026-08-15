<?php

declare(strict_types=1);

namespace App\Mail;

use App\Actions\IssueQuote;
use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteIssued extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Quote $quote) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rifle build quotation · '.$this->quote->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.quote-issued',
            with: ['quote' => $this->quote],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $pdf = app(IssueQuote::class)->pdf($this->quote)->output();

        return [
            Attachment::fromData(fn (): string => $pdf, $this->quote->reference.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
