<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TrainingEventStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Published = 'published';
    case Full = 'full';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Full => 'Fully booked',
            self::Cancelled => 'Cancelled',
            self::Completed => 'Completed',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Published => 'success',
            self::Full => 'warning',
            self::Cancelled => 'danger',
            self::Completed => 'info',
        };
    }

    /**
     * Statuses that are visible on the public site.
     * Fully-booked events DO display (as "Fully booked"); drafts/cancelled do not.
     */
    public static function publiclyVisible(): array
    {
        return [self::Published, self::Full];
    }
}
