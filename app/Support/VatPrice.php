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
     * Ex-VAT amount in cents that produced the given inclusive amount.
     * Inverse of includedVatCents(): $inclusive - VAT-portion. Used by the
     * product form's live-linked "Shop price incl. VAT" input to back-fill
     * the stored ex-VAT field when the shopkeeper types the sticker price.
     */
    public static function exVatCents(int $inclusiveCents): int
    {
        if ($inclusiveCents <= 0) {
            return 0;
        }

        return $inclusiveCents - self::includedVatCents($inclusiveCents);
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
}
