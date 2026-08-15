<?php

declare(strict_types=1);

namespace App\Filament\Resources\ComponentCategories;

use App\Filament\Resources\ComponentCategories\Pages\CreateComponentCategory;
use App\Filament\Resources\ComponentCategories\Pages\EditComponentCategory;
use App\Filament\Resources\ComponentCategories\Pages\ListComponentCategories;
use App\Filament\Resources\ComponentCategories\Schemas\ComponentCategoryForm;
use App\Filament\Resources\ComponentCategories\Tables\ComponentCategoriesTable;
use App\Models\ComponentCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ComponentCategoryResource extends Resource
{
    protected static ?string $model = ComponentCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|\UnitEnum|null $navigationGroup = 'Rifle Builder';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Categories';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ComponentCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ComponentCategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComponentCategories::route('/'),
            'create' => CreateComponentCategory::route('/create'),
            'edit' => EditComponentCategory::route('/{record}/edit'),
        ];
    }
}
