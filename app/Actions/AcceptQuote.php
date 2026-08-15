<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\QuoteStatus;
use App\Models\Quote;
use Illuminate\Support\Facades\DB;

class AcceptQuote
{
    public function handle(Quote $quote): Quote
    {
        return DB::transaction(function () use ($quote): Quote {
            if ($quote->status !== QuoteStatus::Accepted && $quote->status !== QuoteStatus::Converted) {
                $quote->update(['status' => QuoteStatus::Accepted]);
            }

            $quote->payment()->firstOrCreate([], [
                'method' => PaymentMethod::Eft->value,
                'amount_cents' => $quote->depositCents(),
                'status' => PaymentStatus::Pending->value,
                'reference' => $quote->reference,
            ]);

            return $quote->fresh('payment');
        });
    }
}
