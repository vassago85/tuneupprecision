<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum QuoteStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Expired = 'expired';
    case Converted = 'converted';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Sent => 'info',
            self::Accepted => 'warning',
            self::Expired => 'danger',
            self::Converted => 'success',
        };
    }
}
