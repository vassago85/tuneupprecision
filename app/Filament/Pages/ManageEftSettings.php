<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageEftSettings extends Page
{
    protected string $view = 'filament.pages.manage-eft-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Settings';

    protected static ?string $title = 'EFT & reference settings';

    /**
     * @return array<string, mixed>
     */
    public function getEft(): array
    {
        return config('tuneup.eft');
    }

    /**
     * @return array<string, string>
     */
    public function getReferences(): array
    {
        return config('tuneup.references');
    }
}
