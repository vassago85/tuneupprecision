<?php

declare(strict_types=1);

namespace App\Filament\Resources\ComponentCategories\Schemas;

use App\Enums\ComponentSelectionMode;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ComponentCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('key', Str::slug((string) $state))),
                TextInput::make('key')
                    ->required()
                    ->unique(ignoreRecord: true),
                Textarea::make('hint')
                    ->rows(2)
                    ->columnSpanFull(),
                Select::make('selection_mode')
                    ->options(ComponentSelectionMode::class)
                    ->required()
                    ->default(ComponentSelectionMode::Single),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0),
                Toggle::make('is_optional')
                    ->label('Optional step'),
                Toggle::make('allows_quantity')
                    ->label('Allows quantity'),
                Toggle::make('is_hidden')
                    ->label('Hidden (labour)')
                    ->helperText('Hidden categories do not appear as builder steps.'),
            ]);
    }
}
