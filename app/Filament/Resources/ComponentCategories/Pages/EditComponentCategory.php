<?php

declare(strict_types=1);

namespace App\Filament\Resources\ComponentCategories\Pages;

use App\Filament\Resources\ComponentCategories\ComponentCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditComponentCategory extends EditRecord
{
    protected static string $resource = ComponentCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
