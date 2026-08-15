<?php

declare(strict_types=1);

namespace App\Filament\Resources\Components\Tables;

use App\Models\Component;
use App\Support\Money;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class ComponentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->defaultGroup('category.name')
            ->columns([
                TextColumn::make('brand')
                    ->searchable()
                    ->color('gray'),
                TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('price_cents')
                    ->label('Retail')
                    ->formatStateUsing(fn (int $state): string => Money::format($state))
                    ->sortable(),
                TextColumn::make('cost_cents')
                    ->label('Cost')
                    ->formatStateUsing(fn (int $state): string => Money::format($state))
                    ->toggleable(),
                TextColumn::make('gp')
                    ->label('GP%')
                    ->state(fn (Component $record): string => number_format($record->grossProfitPercent(), 0).'%'),
                ToggleColumn::make('is_active')
                    ->label('Active'),
                IconColumn::make('is_automatic')
                    ->boolean()
                    ->label('Labour')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('component_category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),
                TernaryFilter::make('is_automatic')
                    ->label('Labour lines')
                    ->trueLabel('Automatic only')
                    ->falseLabel('Picker only')
                    ->placeholder('All'),
                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('adjustPrices')
                        ->label('Adjust prices')
                        ->icon('heroicon-o-banknotes')
                        ->schema([
                            TextInput::make('percent')
                                ->label('Adjust retail by %')
                                ->numeric()
                                ->helperText('e.g. 5 to raise 5%, -5 to drop 5%.'),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $percent = (float) ($data['percent'] ?? 0);
                            foreach ($records as $record) {
                                $record->update([
                                    'price_cents' => max(0, (int) round($record->price_cents * (1 + $percent / 100))),
                                ]);
                            }
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
