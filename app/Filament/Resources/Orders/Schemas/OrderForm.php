<?php

declare(strict_types=1);

namespace App\Filament\Resources\Orders\Schemas;

use App\Enums\OrderStatus;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Auto-generated (TU-S-XXXXXX)'),
                TextInput::make('customer_name')
                    ->required(),
                TextInput::make('email')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('subtotal_cents')
                    ->label('Subtotal')
                    ->prefix('R')
                    ->numeric()
                    ->required()
                    ->formatStateUsing(fn (?int $state): float => (int) $state / 100)
                    ->dehydrateStateUsing(fn ($state): int => (int) round(((float) $state) * 100)),
                Select::make('status')
                    ->options(OrderStatus::class)
                    ->default(OrderStatus::Pending)
                    ->required(),
                Repeater::make('orderItems')
                    ->relationship()
                    ->label('Order items (snapshots)')
                    ->helperText('Line items snapshot the product at purchase time so past orders never mutate.')
                    ->schema([
                        TextInput::make('name_snapshot')
                            ->label('Item')
                            ->required(),
                        TextInput::make('price_cents_snapshot')
                            ->label('Unit price')
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
                    ->columnSpanFull()
                    ->defaultItems(0),
            ]);
    }
}
