<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrainingEvents\Schemas;

use App\Enums\TrainingEventStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TrainingEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('course_template_id')
                    ->relationship('courseTemplate', 'title')
                    ->required()
                    ->searchable()
                    ->preload(),
                DatePicker::make('starts_on')
                    ->required(),
                DatePicker::make('ends_on')
                    ->helperText('Leave blank for single-day courses.'),
                TextInput::make('venue')
                    ->required()
                    ->default('Private range · Gauteng'),
                TextInput::make('capacity')
                    ->required()
                    ->numeric()
                    ->default(6),
                TextInput::make('seats_taken')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->helperText('Reserved seats (holds + confirmed).'),
                TextInput::make('price_cents')
                    ->label('Price override')
                    ->prefix('R')
                    ->numeric()
                    ->helperText('Leave blank to use the template base price.')
                    ->formatStateUsing(fn (?int $state): ?float => $state === null ? null : $state / 100)
                    ->dehydrateStateUsing(fn ($state): ?int => filled($state) ? (int) round(((float) $state) * 100) : null),
                Select::make('status')
                    ->options(TrainingEventStatus::class)
                    ->default(TrainingEventStatus::Draft)
                    ->required(),
            ]);
    }
}
