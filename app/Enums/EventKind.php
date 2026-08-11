<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EventKind: string implements HasColor, HasLabel
{
    /** A dated instance of a training course (has a CourseTemplate). */
    case Training = 'training';

    /** A competition/match Dirk is attending that guests can join (RSVP, no course). */
    case Competition = 'competition';

    public function getLabel(): string
    {
        return match ($this) {
            self::Training => 'Training',
            self::Competition => 'Competition',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Training => 'info',
            self::Competition => 'warning',
        };
    }
}
