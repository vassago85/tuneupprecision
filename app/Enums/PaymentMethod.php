<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentMethod: string implements HasLabel
{
    case Eft = 'eft';
    case Gateway = 'gateway';

    public function getLabel(): string
    {
        return match ($this) {
            self::Eft => 'EFT',
            self::Gateway => 'Gateway',
        };
    }
}
