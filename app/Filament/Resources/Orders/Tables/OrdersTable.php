<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Filament\Support\MarkAsPaidAction;
use App\Support\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->searchable()
                    ->weight('bold')
                    ->copyable(),
                TextColumn::make('customer_name')
                    ->searchable(),
                TextColumn::make('order_items_count')
                    ->label('Items')
                    ->counts('orderItems')
                    ->badge(),
                TextColumn::make('subtotal_cents')
                    ->label('Subtotal')
                    ->formatStateUsing(fn (int $state): string => Money::format($state))
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(OrderStatus::class),
            ])
            ->recordActions([
                MarkAsPaidAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
