<?php

declare(strict_types=1);

namespace App\Filament\Resources\Quotes\Schemas;

use App\Enums\RiflePlatform;
use App\Livewire\RifleBuildPicker;
use App\Support\Money;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer')
                    ->columns(2)
                    ->schema([
                        TextInput::make('customer_name')
                            ->required(),
                        TextInput::make('customer_email')
                            ->email()
                            ->required(),
                        TextInput::make('customer_phone'),
                        TextInput::make('licence_status')
                            ->placeholder('Dedicated sport shooter'),
                    ]),
                Section::make('Build')
                    ->schema([
                        Select::make('platform')
                            ->options(RiflePlatform::class)
                            ->required()
                            ->live()
                            ->default(RiflePlatform::Barrelled),
                        Livewire::make(RifleBuildPicker::class, fn ($record): array => [
                            'dealerMode' => true,
                            'quoteId' => $record?->id,
                        ]),
                    ]),
                Section::make('Commercial')
                    ->columns(3)
                    ->schema([
                        TextInput::make('discount_percent_ui')
                            ->label('Discount %')
                            ->numeric()
                            ->default(0)
                            ->dehydrated(false),
                        TextInput::make('discount_amount_cents')
                            ->label('Discount (rand)')
                            ->prefix('R')
                            ->numeric()
                            ->formatStateUsing(fn (?int $state): float => (int) $state / 100)
                            ->dehydrateStateUsing(fn ($state): int => (int) round(((float) $state) * 100)),
                        TextInput::make('deposit_percent')
                            ->label('Deposit %')
                            ->numeric()
                            ->default(50)
                            ->required(),
                        TextInput::make('lead_time'),
                        DatePicker::make('valid_until'),
                        TextInput::make('total_cents')
                            ->label('Total')
                            ->disabled()
                            ->formatStateUsing(fn (?int $state): string => $state ? Money::format($state) : '—')
                            ->dehydrated(false),
                        Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
