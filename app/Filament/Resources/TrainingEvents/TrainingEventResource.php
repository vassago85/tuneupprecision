<?php

namespace App\Filament\Resources\TrainingEvents;

use App\Filament\Resources\TrainingEvents\Pages\CreateTrainingEvent;
use App\Filament\Resources\TrainingEvents\Pages\EditTrainingEvent;
use App\Filament\Resources\TrainingEvents\Pages\ListTrainingEvents;
use App\Filament\Resources\TrainingEvents\RelationManagers\RsvpsRelationManager;
use App\Filament\Resources\TrainingEvents\Schemas\TrainingEventForm;
use App\Filament\Resources\TrainingEvents\Tables\TrainingEventsTable;
use App\Models\TrainingEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TrainingEventResource extends Resource
{
    protected static ?string $model = TrainingEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static string|\UnitEnum|null $navigationGroup = 'Training';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Events';

    public static function form(Schema $schema): Schema
    {
        return TrainingEventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TrainingEventsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RsvpsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTrainingEvents::route('/'),
            'create' => CreateTrainingEvent::route('/create'),
            'edit' => EditTrainingEvent::route('/{record}/edit'),
        ];
    }
}
