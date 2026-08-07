<?php

namespace App\Filament\Resources\TrainingTypes\Pages;

use App\Filament\Resources\TrainingTypes\TrainingTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTrainingType extends EditRecord
{
    protected static string $resource = TrainingTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
