<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use App\Http\Controllers\ShopController;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('reference')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated (TU-S-XXXXXX)'),
                        Select::make('status')
                            ->options(OrderStatus::class)
                            ->default(OrderStatus::Pending)
                            ->required()
                            ->helperText('Use Mark as paid and Mark as sent on the order. Those email the customer. Changing the status here does not.'),
                        TextInput::make('customer_name')
                            ->required(),
                        TextInput::make('email')
                            ->email()
                            ->required(),
                        TextInput::make('phone')
                            ->tel(),
                    ]),
                Section::make('Delivery')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('address_line_1')
                            ->label('Street address')
                            ->columnSpanFull(),
                        TextInput::make('address_line_2')
                            ->label('Complex or unit')
                            ->columnSpanFull(),
                        TextInput::make('suburb'),
                        TextInput::make('city'),
                        Select::make('province')
                            ->options(array_combine(ShopController::PROVINCES, ShopController::PROVINCES))
                            ->searchable(),
                        TextInput::make('postal_code')
                            ->label('Postal code'),
                    ]),
                Section::make('Amounts')
                    ->description('Figures are VAT-inclusive rands, as the customer was charged.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('subtotal_cents')
                            ->label('Goods')
                            ->prefix('R')
                            ->numeric()
                            ->required()
                            ->formatStateUsing(fn (?int $state): float => (int) $state / 100)
                            ->dehydrateStateUsing(fn ($state): int => (int) round(((float) $state) * 100)),
                        TextInput::make('shipping_cents')
                            ->label('Delivery')
                            ->prefix('R')
                            ->numeric()
                            ->formatStateUsing(fn (?int $state): float => (int) $state / 100)
                            ->dehydrateStateUsing(fn ($state): int => (int) round(((float) $state) * 100)),
                    ]),
                Section::make('Line items')
                    ->description('Snapshots from the moment of purchase. Editing a line does not change the product or the stock.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('orderItems')
                            ->relationship()
                            ->hiddenLabel()
                            ->schema([
                                TextInput::make('name_snapshot')
                                    ->label('Item')
                                    ->required(),
                                TextInput::make('price_cents_snapshot')
                                    ->label('Unit price incl. VAT')
                                    ->prefix('R')
                                    ->numeric()
                                    ->required()
                                    ->formatStateUsing(fn (?int $state): float => (int) $state / 100)
                                    ->dehydrateStateUsing(fn ($state): int => (int) round(((float) $state) * 100)),
                                TextInput::make('qty')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),
                            ])
                            ->columns(3)
                            ->defaultItems(0),
                    ]),
            ]);
    }
}
