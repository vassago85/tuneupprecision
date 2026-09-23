<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Turns an ex-VAT amount in cents into the VAT-inclusive shop price.
 * The rate comes from config('legal.vat_rate') (15%).
 */
final class VatPrice
{
    public static function rate(): float
    {
        return (float) config('legal.vat_rate', 0.15);
    }

    public static function percentLabel(): string
    {
        return (int) round(self::rate() * 100).'%';
    }

    /**
     * VAT already included in an inclusive amount, in cents.
     */
    public static function includedVatCents(int $inclusiveCents): int
    {
        if ($inclusiveCents <= 0) {
            return 0;
        }

        $basisPoints = (int) round(self::rate() * 10000);
        $denominator = 10000 + $basisPoints;

        return intdiv($inclusiveCents * $basisPoints + intdiv($denominator, 2), $denominator);
    }

    /**
     * Inclusive cents from an ex-VAT amount in cents.
     *
     * When $roundUp is true the result is raised to the next whole rand.
     * A price that is already a whole rand stays as it is.
     */
    public static function inclusiveCents(int $exVatCents, bool $roundUp): int
    {
        if ($exVatCents < 0) {
            $exVatCents = 0;
        }

        // 0.15 → 1500. Scaled units are 1/10000 of a cent so the multiply stays exact.
        $basisPoints = (int) round(self::rate() * 10000);
        $scaled = $exVatCents * (10000 + $basisPoints);

        if ($roundUp) {
            $perRand = 100 * 10000;

            return intdiv($scaled + $perRand - 1, $perRand) * 100;
        }

        return intdiv($scaled + 5000, 10000);
    }

    /**
     * Preview line for the product form. $exVatRands is what was typed.
     */
    public static function preview(mixed $exVatRands, bool $roundUp): string
    {
        if ($exVatRands === null || $exVatRands === '') {
            return 'Enter a selling price ex VAT.';
        }

        $ex = Money::toCents($exVatRands);
        $exact = self::inclusiveCents($ex, false);
        $shop = self::inclusiveCents($ex, $roundUp);
        $vat = $exact - $ex;

        $line = 'VAT @ '.self::percentLabel().' '.Money::format($vat).' · Shop price '.Money::format($shop);

        if ($roundUp && $shop !== $exact) {
            $line .= ' (rounded up from '.Money::format($exact).')';
        }

        return $line;
    }
}
