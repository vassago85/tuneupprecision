<?php

namespace App\Filament\Resources\TrainingEvents\Pages;

use App\Filament\Resources\TrainingEvents\TrainingEventResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTrainingEvent extends EditRecord
{
    protected static string $resource = TrainingEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
