<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Money is always stored as integer cents (ZAR). This helper is the single
 * place that knows how to turn cents into something a human reads.
 */
final class Money
{
    /**
     * Format integer cents as a ZAR string, e.g. 185000 => "R1 850.00".
     */
    public static function format(int $cents, bool $withDecimals = true): string
    {
        $rands = $cents / 100;

        $formatted = number_format($rands, $withDecimals ? 2 : 0, '.', ' ');

        return 'R'.$formatted;
    }

    /**
     * Convert a rand amount (float|string) into integer cents.
     */
    public static function toCents(float|int|string $rands): int
    {
        return (int) round(((float) $rands) * 100);
    }
}
