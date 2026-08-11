<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('category')
                    ->placeholder('Headwear'),
                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                TextInput::make('price_cents')
                    ->label('Price')
                    ->prefix('R')
                    ->numeric()
                    ->required()
                    ->helperText('Stored as integer cents.')
                    ->formatStateUsing(fn (?int $state): float => (int) $state / 100)
                    ->dehydrateStateUsing(fn ($state): int => (int) round(((float) $state) * 100)),
                TextInput::make('stock_qty')
                    ->label('Stock quantity')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->helperText('Out-of-stock products are simply hidden from the shop.'),
                Toggle::make('is_active')
                    ->label('Published')
                    ->default(true),
                SpatieMediaLibraryFileUpload::make('images')
                    ->collection('images')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    // Compress before storing: cap the original at 2000px.
                    ->imageResizeMode('contain')
                    ->imageResizeUpscale(false)
                    ->imageResizeTargetWidth('2000')
                    ->imageResizeTargetHeight('2000')
                    ->columnSpanFull(),
            ]);
    }
}
