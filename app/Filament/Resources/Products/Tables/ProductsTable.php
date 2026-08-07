<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Tables;

use App\Support\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->label('')
                    ->collection('images')
                    ->conversion('thumb')
                    ->circular(),
                TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('category')
                    ->searchable()
                    ->color('gray'),
                TextColumn::make('price_cents')
                    ->label('Price')
                    ->formatStateUsing(fn (int $state): string => Money::format($state))
                    ->sortable(),
                TextColumn::make('stock_qty')
                    ->label('Stock')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 5 => 'warning',
                        default => 'success',
                    })
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Published'),
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
