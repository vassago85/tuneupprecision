<?php

declare(strict_types=1);

namespace App\Filament\Resources\ComponentCategories\Pages;

use App\Filament\Resources\ComponentCategories\ComponentCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListComponentCategories extends ListRecords
{
    protected static string $resource = ComponentCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
