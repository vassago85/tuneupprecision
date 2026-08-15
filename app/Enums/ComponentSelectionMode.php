<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ComponentSelectionMode: string implements HasLabel
{
    case Single = 'single';
    case Multi = 'multi';

    public function getLabel(): string
    {
        return match ($this) {
            self::Single => 'Single',
            self::Multi => 'Multi',
        };
    }
}
