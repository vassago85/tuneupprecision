<?php

declare(strict_types=1);

namespace App\Filament\Resources\TrainingTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TrainingTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state)))
                    ->placeholder('Long Range Prone'),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Used in the public filter URL (?type=slug).'),
                Textarea::make('blurb')
                    ->rows(2)
                    ->columnSpanFull()
                    ->helperText('Short description of this training discipline.'),
                TextInput::make('icon')
                    ->placeholder('heroicon-o-viewfinder-circle')
                    ->helperText('Optional Heroicon name for admin display.'),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers show first.'),
                Toggle::make('is_active')
                    ->default(true),
            ]);
    }
}
