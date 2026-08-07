<?php

declare(strict_types=1);

namespace App\Filament\Resources\CourseTemplates\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CourseTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('training_type_id')
                    ->label('Training type')
                    ->relationship('trainingType', 'name')
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')->required(),
                    ])
                    ->helperText('e.g. Reloading, PRS, Long Range Prone.'),
                TextInput::make('title')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Used in the URL. Auto-filled from the title.'),
                TextInput::make('level')
                    ->placeholder('Level 01 · Foundation'),
                Textarea::make('blurb')
                    ->rows(3)
                    ->columnSpanFull(),
                KeyValue::make('specs')
                    ->label('Spec sheet (DOPE card)')
                    ->keyLabel('Label')
                    ->valueLabel('Value')
                    ->helperText('e.g. Duration → 1 day · 08:00–16:00')
                    ->columnSpanFull(),
                TextInput::make('base_price_cents')
                    ->label('Base price')
                    ->prefix('R')
                    ->numeric()
                    ->required()
                    ->helperText('Stored as integer cents.')
                    ->formatStateUsing(fn (?int $state): float => (int) $state / 100)
                    ->dehydrateStateUsing(fn ($state): int => (int) round(((float) $state) * 100)),
                TextInput::make('default_capacity')
                    ->required()
                    ->numeric()
                    ->default(6),
                Toggle::make('is_active')
                    ->default(true)
                    ->helperText('Inactive templates are hidden from the public site.'),
            ]);
    }
}
