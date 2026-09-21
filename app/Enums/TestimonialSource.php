<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * How a testimonial landed in the system.
 * - Manual: Dirk typed it in via the Filament resource (backfilling old wins).
 * - Shooter: submitted via the public signed-URL form after a training day.
 */
enum TestimonialSource: string implements HasColor, HasLabel
{
    case Manual = 'manual';
    case Shooter = 'shooter';

    public function getLabel(): string
    {
        return match ($this) {
            self::Manual => 'Added by admin',
            self::Shooter => 'From shooter',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Manual => 'gray',
            self::Shooter => 'info',
        };
    }
}
