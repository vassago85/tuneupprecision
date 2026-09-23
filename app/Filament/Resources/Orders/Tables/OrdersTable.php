<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Tables;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Support\CancelOrderAction;
use App\Filament\Support\DownloadInvoiceAction;
use App\Filament\Support\FulfillOrderAction;
use App\Filament\Support\MarkAsPaidAction;
use App\Models\Order;
use App\Support\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('payment'))
            ->recordUrl(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('reference')
                    ->searchable()
                    ->weight('bold')
                    ->copyable(),
                TextColumn::make('customer_name')
                    ->searchable()
                    ->description(fn (Order $record): string => (string) $record->email),
                TextColumn::make('order_items_count')
                    ->label('Items')
                    ->counts('orderItems')
                    ->badge(),
                TextColumn::make('city')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->state(fn (Order $record): string => Money::format($record->totalCents())),
                TextColumn::make('payment.status')
                    ->label('Payment')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Placed')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(OrderStatus::class),
            ])
            ->recordActions([
                ViewAction::make()->label('Open'),
                MarkAsPaidAction::make(),
                FulfillOrderAction::make(),
                DownloadInvoiceAction::make(),
                CancelOrderAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
