<?php

declare(strict_types=1);

namespace App\Filament\Resources\Bookings\Schemas;

use App\Enums\BookingStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('training_event_id')
                    ->relationship('trainingEvent', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record): string => ($record->courseTemplate?->title ?? 'Event').' · '.$record->starts_on?->format('d M Y'))
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('customer_name')
                    ->required(),
                TextInput::make('email')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('rifle')
                    ->helperText('Optional — the rifle the shooter is bringing.'),
                TextInput::make('seats')
                    ->numeric()
                    ->default(1)
                    ->required(),
                TextInput::make('reference')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Auto-generated (TU-B-XXXXXX)'),
                TextInput::make('amount_cents')
                    ->label('Amount')
                    ->prefix('R')
                    ->numeric()
                    ->required()
                    ->formatStateUsing(fn (?int $state): float => (int) $state / 100)
                    ->dehydrateStateUsing(fn ($state): int => (int) round(((float) $state) * 100)),
                Select::make('status')
                    ->options(BookingStatus::class)
                    ->default(BookingStatus::Pending)
                    ->required(),
                DateTimePicker::make('hold_expires_at')
                    ->helperText('Pending holds past this time are released by the scheduler.'),
            ]);
    }
}
