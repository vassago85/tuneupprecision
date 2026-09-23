<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use App\Support\Money;
use App\Support\VatPrice;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Shop name')
                    ->required()
                    ->maxLength(120)
                    ->live(onBlur: true)
                    ->helperText('The name customers see. The supplier code stays in the SKU.')
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state))),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('sku')
                    ->label('SKU')
                    ->unique(ignoreRecord: true),
                TextInput::make('category')
                    ->placeholder('Hearing'),
                Textarea::make('description')
                    ->label('Short description')
                    ->rows(2)
                    ->maxLength(180)
                    ->helperText('Optional. One or two lines under the name on the shop card.')
                    ->columnSpanFull(),
                Section::make('Pricing')
                    ->description('Nett cost and the selling price are ex VAT. The shop price is the selling price plus '.VatPrice::percentLabel().' VAT.')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('cost_cents')
                            ->label('Nett cost ex VAT')
                            ->prefix('R')
                            ->numeric()
                            ->required()
                            ->formatStateUsing(fn ($state): float => (int) $state / 100)
                            ->dehydrateStateUsing(fn ($state): int => Money::toCents($state ?? 0)),
                        TextInput::make('selling_ex_vat_cents')
                            ->label('Selling price ex VAT')
                            ->prefix('R')
                            ->numeric()
                            ->live(onBlur: true)
                            ->helperText('Leave blank to keep an existing shop price that was entered directly.')
                            ->formatStateUsing(fn ($state): ?float => $state === null || $state === '' ? null : ((int) $state) / 100)
                            ->dehydrateStateUsing(fn ($state): ?int => blank($state) ? null : Money::toCents($state)),
                        Toggle::make('round_price_up')
                            ->label('Round up to the next rand')
                            ->live()
                            ->helperText('Raises the VAT-inclusive price to the next whole rand. A price that is already a whole rand stays as it is.'),
                        Placeholder::make('price_incl_vat')
                            ->label('Shop price incl. VAT')
                            ->content(fn (Get $get): string => VatPrice::preview(
                                $get('selling_ex_vat_cents'),
                                (bool) $get('round_price_up'),
                            )),
                    ]),
                TextInput::make('stock_qty')
                    ->label('Stock quantity')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->helperText('Out-of-stock products are hidden from the shop.'),
                Toggle::make('is_active')
                    ->label('Published')
                    ->default(true)
                    ->helperText('Unpublished until you are ready. The shop also hides a product with no selling price.'),
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
