<?php

namespace App\Filament\Resources\CourseTemplates;

use App\Filament\Resources\CourseTemplates\Pages\CreateCourseTemplate;
use App\Filament\Resources\CourseTemplates\Pages\EditCourseTemplate;
use App\Filament\Resources\CourseTemplates\Pages\ListCourseTemplates;
use App\Filament\Resources\CourseTemplates\Schemas\CourseTemplateForm;
use App\Filament\Resources\CourseTemplates\Tables\CourseTemplatesTable;
use App\Models\CourseTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CourseTemplateResource extends Resource
{
    protected static ?string $model = CourseTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Training';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return CourseTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CourseTemplatesTable::configure($table);
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
            'index' => ListCourseTemplates::route('/'),
            'create' => CreateCourseTemplate::route('/create'),
            'edit' => EditCourseTemplate::route('/{record}/edit'),
        ];
    }
}
