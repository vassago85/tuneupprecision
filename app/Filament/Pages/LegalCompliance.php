<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Support\LegalIdentity;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class LegalCompliance extends Page
{
    protected string $view = 'filament.pages.legal-compliance';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Legal & Compliance';

    protected static ?string $title = 'Legal & Compliance';

    /**
     * @return array<string, mixed>
     */
    public function getIdentity(): array
    {
        return LegalIdentity::effective();
    }

    /**
     * @return list<string>
     */
    public function getFailures(): array
    {
        return LegalIdentity::failures();
    }

    /**
     * @return array<string, string>
     */
    public function getUpdated(): array
    {
        return config('legal.updated', []);
    }
}
