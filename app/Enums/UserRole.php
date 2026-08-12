<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasColor, HasLabel
{
    /** Dirk. Full access to the /admin Filament panel. */
    case Admin = 'admin';

    /** Public member account. Never touches the admin panel. */
    case Member = 'member';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Member => 'Member',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Admin => 'warning',
            self::Member => 'gray',
        };
    }
}
