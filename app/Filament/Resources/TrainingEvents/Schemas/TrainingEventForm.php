<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrainingEvents\Schemas;

use App\Enums\EventKind;
use App\Enums\TrainingEventStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class TrainingEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('kind')
                    ->label('Event type')
                    ->options(EventKind::class)
                    ->default(EventKind::Training)
                    ->required()
                    ->live()
                    ->helperText('Training = a scheduled course. Competition = a match Dirk is attending that guests can join.'),

                Section::make('Course')
                    ->visible(fn (Get $get): bool => $get('kind') === EventKind::Training->value)
                    ->schema([
                        Select::make('course_template_id')
                            ->relationship('courseTemplate', 'title')
                            ->required(fn (Get $get): bool => $get('kind') === EventKind::Training->value)
                            ->searchable()
                            ->preload(),
                    ]),

                Section::make('Competition details')
                    ->visible(fn (Get $get): bool => $get('kind') === EventKind::Competition->value)
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Match / competition name')
                            ->placeholder('Bloem Precision Series · Round 3')
                            ->required(fn (Get $get): bool => $get('kind') === EventKind::Competition->value)
                            ->columnSpanFull(),
                        Select::make('training_type_id')
                            ->label('Discipline')
                            ->relationship('trainingType', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('e.g. PRS, ELR.'),
                        TextInput::make('entry_fee_cents')
                            ->label('Entry fee')
                            ->prefix('R')
                            ->numeric()
                            ->helperText('Informational — guests RSVP for free on the site.')
                            ->formatStateUsing(fn (?int $state): ?float => $state === null ? null : $state / 100)
                            ->dehydrateStateUsing(fn ($state): ?int => filled($state) ? (int) round(((float) $state) * 100) : null),
                        TextInput::make('dirk_role')
                            ->label("Dirk's role")
                            ->placeholder('Dirk competing — squad with guests')
                            ->columnSpanFull(),
                        TextInput::make('external_url')
                            ->label('External link')
                            ->url()
                            ->placeholder('https://…')
                            ->helperText('Match registration or results page.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Schedule')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('starts_on')
                            ->required(),
                        DatePicker::make('ends_on')
                            ->helperText('Leave blank for single-day events.'),
                        TextInput::make('venue')
                            ->required()
                            ->default('Private range · Gauteng'),
                        TextInput::make('capacity')
                            ->required()
                            ->numeric()
                            ->default(6)
                            ->helperText(fn (Get $get): string => $get('kind') === EventKind::Competition->value
                                ? 'Max guests who can join.'
                                : 'Seats available on the course.'),
                        TextInput::make('seats_taken')
                            ->label(fn (Get $get): string => $get('kind') === EventKind::Competition->value ? 'Guests joined' : 'Seats taken')
                            ->required()
                            ->numeric()
                            ->default(0),
                        TextInput::make('price_cents')
                            ->label('Price override')
                            ->prefix('R')
                            ->numeric()
                            ->visible(fn (Get $get): bool => $get('kind') === EventKind::Training->value)
                            ->helperText('Leave blank to use the template base price.')
                            ->formatStateUsing(fn (?int $state): ?float => $state === null ? null : $state / 100)
                            ->dehydrateStateUsing(fn ($state): ?int => filled($state) ? (int) round(((float) $state) * 100) : null),
                        Select::make('status')
                            ->options(TrainingEventStatus::class)
                            ->default(TrainingEventStatus::Draft)
                            ->required(),
                    ]),
            ]);
    }
}
