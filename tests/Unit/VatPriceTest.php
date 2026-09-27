<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\VatPrice;
use Tests\TestCase;

class VatPriceTest extends TestCase
{
    public function test_adds_fifteen_percent_and_keeps_cents(): void
    {
        // R1 495.00 ex VAT → R1 719.25 incl.
        $this->assertSame(171925, VatPrice::inclusiveCents(149500, false));
    }

    public function test_round_up_lifts_to_the_next_rand(): void
    {
        $this->assertSame(172000, VatPrice::inclusiveCents(149500, true));
    }

    public function test_round_up_leaves_an_exact_rand_alone(): void
    {
        // R380.00 × 1.15 = R437.00 exactly.
        $this->assertSame(43700, VatPrice::inclusiveCents(38000, true));
        $this->assertSame(43700, VatPrice::inclusiveCents(38000, false));
    }

    public function test_small_amount_rounds_up_from_a_half_rand(): void
    {
        // R70.00 × 1.15 = R80.50 → R81.00 when rounded up.
        $this->assertSame(8050, VatPrice::inclusiveCents(7000, false));
        $this->assertSame(8100, VatPrice::inclusiveCents(7000, true));
    }

    public function test_ex_vat_round_trips_through_the_inclusive_amount(): void
    {
        // Backs the product form's live-linked "Shop price incl. VAT" input:
        // typing an inclusive price must resolve to the same ex-VAT cents that
        // produced it in the first place.
        $ex = 20000; // R200.00
        $incl = VatPrice::inclusiveCents($ex, false); // R230.00 at 15%

        $this->assertSame($ex, VatPrice::exVatCents($incl));
        // And the VAT portion equals the difference — trivial identity check.
        $this->assertSame($incl - $ex, VatPrice::includedVatCents($incl));
    }

    public function test_ex_vat_of_zero_or_negative_returns_zero(): void
    {
        $this->assertSame(0, VatPrice::exVatCents(0));
        $this->assertSame(0, VatPrice::exVatCents(-500));
    }
}
