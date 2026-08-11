<?php

declare(strict_types=1);

namespace App\Filament\Resources\CourseTemplates\Tables;

use App\Support\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CourseTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('thumbnail')
                    ->label('')
                    ->collection('thumbnail')
                    ->conversion('thumb')
                    ->square(),
                TextColumn::make('title')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('trainingType.name')
                    ->label('Type')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('level')
                    ->searchable()
                    ->color('gray'),
                TextColumn::make('base_price_cents')
                    ->label('Base price')
                    ->formatStateUsing(fn (int $state): string => Money::format($state))
                    ->sortable(),
                TextColumn::make('training_events_count')
                    ->label('Events')
                    ->counts('trainingEvents')
                    ->badge(),
                TextColumn::make('default_capacity')
                    ->label('Capacity')
                    ->numeric(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
