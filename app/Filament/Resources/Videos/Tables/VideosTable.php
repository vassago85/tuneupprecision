<?php

declare(strict_types=1);

namespace App\Filament\Resources\Videos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class VideosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                SpatieMediaLibraryImageColumn::make('poster')
                    ->label('')
                    ->collection('poster')
                    ->conversion('thumb'),
                TextColumn::make('title')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('trainingType.name')
                    ->label('Discipline')
                    ->badge()
                    ->color('gray')
                    ->placeholder('Uncategorised'),
                TextColumn::make('source')
                    ->label('Source')
                    ->badge()
                    ->state(fn ($record): string => $record->hasNativeVideo()
                        ? 'MP4'
                        : ($record->youtube_id ? 'YouTube' : '—'))
                    ->color(fn (string $state): string => match ($state) {
                        'MP4' => 'success',
                        'YouTube' => 'info',
                        default => 'danger',
                    }),
                ToggleColumn::make('is_featured')->label('Featured'),
                ToggleColumn::make('is_members_only')->label('Members'),
                ToggleColumn::make('is_active')->label('Published'),
                TextColumn::make('sort_order')
                    ->label('Sort')
                    ->sortable(),
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
