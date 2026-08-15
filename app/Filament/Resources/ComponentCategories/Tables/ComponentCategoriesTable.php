<?php

declare(strict_types=1);

namespace App\Filament\Resources\ComponentCategories\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ComponentCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('key')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('selection_mode')
                    ->badge(),
                IconColumn::make('is_optional')
                    ->boolean()
                    ->label('Optional'),
                IconColumn::make('is_hidden')
                    ->boolean()
                    ->label('Hidden'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
