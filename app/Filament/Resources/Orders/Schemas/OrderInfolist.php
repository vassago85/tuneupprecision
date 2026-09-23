<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use App\Models\OrderItem;
use App\Support\Money;
use App\Support\VatPrice;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Customer')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('customer_name')
                            ->label('Name'),
                        TextEntry::make('email')
                            ->copyable(),
                        TextEntry::make('phone')
                            ->placeholder('—'),
                        TextEntry::make('status')
                            ->badge(),
                        TextEntry::make('created_at')
                            ->label('Placed')
                            ->dateTime('d M Y H:i'),
                    ]),
                Section::make('Deliver to')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('delivery_address')
                            ->hiddenLabel()
                            ->state(fn (Order $record): string => $record->deliveryAddress() ?: '—')
                            ->placeholder('—'),
                    ]),
                Section::make('Items')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('orderItems')
                            ->hiddenLabel()
                            ->contained(false)
                            ->table([
                                TableColumn::make('Item'),
                                TableColumn::make('Qty')->alignment(Alignment::End),
                                TableColumn::make('Unit incl. VAT')->alignment(Alignment::End),
                                TableColumn::make('Amount')->alignment(Alignment::End),
                            ])
                            ->schema([
                                TextEntry::make('name_snapshot'),
                                TextEntry::make('qty'),
                                TextEntry::make('price_cents_snapshot')
                                    ->formatStateUsing(fn (int $state): string => Money::format($state)),
                                TextEntry::make('line_total')
                                    ->state(fn (OrderItem $record): string => $record->lineTotal),
                            ]),
                    ]),
                Section::make('Payment')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('subtotal_cents')
                            ->label('Goods incl. VAT')
                            ->formatStateUsing(fn (int $state): string => Money::format($state)),
                        TextEntry::make('shipping_cents')
                            ->label('Delivery')
                            ->formatStateUsing(fn (int $state): string => $state === 0 ? 'Included' : Money::format($state)),
                        TextEntry::make('ex_vat')
                            ->label('Excl. VAT')
                            ->state(fn (Order $record): string => Money::format($record->totalCents() - VatPrice::includedVatCents($record->totalCents()))),
                        TextEntry::make('vat')
                            ->label('VAT @ '.VatPrice::percentLabel())
                            ->state(fn (Order $record): string => Money::format(VatPrice::includedVatCents($record->totalCents()))),
                        TextEntry::make('total')
                            ->label('Total paid')
                            ->weight('bold'),
                        TextEntry::make('payment.status')
                            ->label('Payment')
                            ->badge()
                            ->placeholder('No payment yet'),
                        TextEntry::make('payment.paid_at')
                            ->label('Paid on')
                            ->dateTime('d M Y H:i')
                            ->placeholder('—'),
                        TextEntry::make('payment.reference')
                            ->label('Payment reference')
                            ->placeholder('—')
                            ->copyable(),
                    ]),
            ]);
    }
}
