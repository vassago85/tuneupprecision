<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrainingEvents\Tables;

use App\Enums\TrainingEventStatus;
use App\Models\TrainingEvent;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TrainingEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('starts_on')
            ->columns([
                TextColumn::make('courseTemplate.title')
                    ->label('Course')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('starts_on')
                    ->label('Starts')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('venue')
                    ->searchable(),
                TextColumn::make('seats_left')
                    ->label('Seats left')
                    ->badge()
                    ->state(fn (TrainingEvent $record): int => $record->seatsLeft())
                    ->color(fn (TrainingEvent $record): string => $record->isFull() ? 'danger' : 'success'),
                TextColumn::make('capacity')
                    ->numeric(),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(TrainingEventStatus::class),
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
