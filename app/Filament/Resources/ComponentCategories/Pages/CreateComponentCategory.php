<?php

declare(strict_types=1);

namespace App\Filament\Resources\ComponentCategories\Pages;

use App\Filament\Resources\ComponentCategories\ComponentCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateComponentCategory extends CreateRecord
{
    protected static string $resource = ComponentCategoryResource::class;
}
