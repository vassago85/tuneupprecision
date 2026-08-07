<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('payable_type')
                    ->required(),
                TextInput::make('payable_id')
                    ->required()
                    ->numeric(),
                Select::make('method')
                    ->options(PaymentMethod::class)
                    ->default('eft')
                    ->required(),
                TextInput::make('reference'),
                TextInput::make('amount_cents')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('status')
                    ->options(PaymentStatus::class)
                    ->default('pending')
                    ->required(),
                DateTimePicker::make('paid_at'),
                TextInput::make('proof_path'),
                TextInput::make('gateway_ref'),
                TextInput::make('gateway_payload'),
            ]);
    }
}
