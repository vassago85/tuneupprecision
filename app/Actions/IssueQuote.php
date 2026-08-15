<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\QuoteStatus;
use App\Mail\QuoteIssued;
use App\Models\Quote;
use App\Support\BusinessDetails;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class IssueQuote
{
    public function pdf(Quote $quote): \Barryvdh\DomPDF\PDF
    {
        $quote->loadMissing('lines');

        return Pdf::loadView('quotes.pdf', [
            'quote' => $quote,
            'business' => BusinessDetails::details(),
            'lines' => $quote->lines->groupBy('group_label'),
        ])->setPaper('a4');
    }

    public function email(Quote $quote): Quote
    {
        Mail::to($quote->customer_email)->queue(new QuoteIssued($quote));

        if ($quote->status === QuoteStatus::Draft) {
            $quote->update(['status' => QuoteStatus::Sent]);
        }

        return $quote->fresh();
    }
}
