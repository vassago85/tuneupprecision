<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SubscriberStatus: string implements HasColor, HasLabel
{
    case Subscribed = 'subscribed';
    case Unsubscribed = 'unsubscribed';

    public function getLabel(): string
    {
        return ucfirst($this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Subscribed => 'success',
            self::Unsubscribed => 'gray',
        };
    }
}
