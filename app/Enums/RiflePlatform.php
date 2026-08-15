<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RiflePlatform: string implements HasLabel
{
    case Barrelled = 'barrelled';
    case Separate = 'separate';

    public function getLabel(): string
    {
        return match ($this) {
            self::Barrelled => 'Barrelled action',
            self::Separate => 'Action + barrel (chambered in-house)',
        };
    }
}
