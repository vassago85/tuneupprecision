<?php

declare(strict_types=1);

namespace App\Filament\Resources\Components\Pages;

use App\Filament\Resources\Components\Actions\ImportComponentsAction;
use App\Filament\Resources\Components\ComponentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListComponents extends ListRecords
{
    protected static string $resource = ComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportComponentsAction::make(),
            CreateAction::make(),
        ];
    }
}
