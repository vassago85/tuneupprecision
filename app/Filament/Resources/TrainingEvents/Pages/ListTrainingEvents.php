<?php

namespace App\Filament\Resources\TrainingEvents\Pages;

use App\Filament\Resources\TrainingEvents\TrainingEventResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrainingEvents extends ListRecords
{
    protected static string $resource = TrainingEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
