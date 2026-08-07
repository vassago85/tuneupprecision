<?php

namespace App\Filament\Resources\TrainingTypes;

use App\Filament\Resources\TrainingTypes\Pages\CreateTrainingType;
use App\Filament\Resources\TrainingTypes\Pages\EditTrainingType;
use App\Filament\Resources\TrainingTypes\Pages\ListTrainingTypes;
use App\Filament\Resources\TrainingTypes\Schemas\TrainingTypeForm;
use App\Filament\Resources\TrainingTypes\Tables\TrainingTypesTable;
use App\Models\TrainingType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TrainingTypeResource extends Resource
{
    protected static ?string $model = TrainingType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static string|\UnitEnum|null $navigationGroup = 'Training';

    protected static ?int $navigationSort = 0;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return TrainingTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrainingTypes::route('/'),
            'create' => CreateTrainingType::route('/create'),
            'edit' => EditTrainingType::route('/{record}/edit'),
        ];
    }
}
