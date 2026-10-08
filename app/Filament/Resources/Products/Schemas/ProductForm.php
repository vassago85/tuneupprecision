<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use App\Support\Money;
use App\Support\VatPrice;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                    ->description('Enter the selling price either ex VAT or inc VAT — the other box updates on blur. Shop displays the inc-VAT price.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('cost_cents')
                            ->label('Nett cost ex VAT')
                            ->prefix('R')
                            ->numeric()
                            ->required()
                            ->columnSpanFull()
                            ->formatStateUsing(fn ($state): float => (int) $state / 100)
                            ->dehydrateStateUsing(fn ($state): int => Money::toCents($state ?? 0)),

                        // Stored field. Editing it recomputes the inc-VAT sibling
                        // using the current round-up toggle. formatStateUsing/dehydrateStateUsing
                        // convert between the DB's cents and the input's rands.
                        TextInput::make('selling_ex_vat_cents')
                            ->label('Selling price ex VAT')
                            ->prefix('R')
                            ->numeric()
                            ->live(onBlur: true)
                            ->helperText('Leave blank to hide the product from the shop.')
                            ->formatStateUsing(fn ($state): ?float => $state === null || $state === '' ? null : ((int) $state) / 100)
                            ->dehydrateStateUsing(fn ($state): ?int => blank($state) ? null : Money::toCents($state))
                            ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                                if (blank($state)) {
                                    $set('selling_incl_vat', null);

                                    return;
                                }

                                $exCents = Money::toCents($state);
                                $inclCents = VatPrice::inclusiveCents($exCents, (bool) $get('round_price_up'));
                                $inclRands = $inclCents / 100;

                                // Guard: only push when the sibling actually differs. Filament
                                // has its own state-hash guard on afterStateUpdated, but this
                                // avoids even the redundant partial re-render.
                                $current = $get('selling_incl_vat');
                                if ($current !== null && $current !== '' && (float) $current === (float) $inclRands) {
                                    return;
                                }

                                $set('selling_incl_vat', $inclRands);
                            }),

                        // Transient input — never dehydrated. Editing it back-computes the
                        // stored ex-VAT field and clears the round-up toggle (a typed inc-VAT
                        // number IS the shop price, so rounding no longer applies).
                        TextInput::make('selling_incl_vat')
                            ->label('Shop price incl. VAT')
                            ->prefix('R')
                            ->numeric()
                            ->live(onBlur: true)
                            ->dehydrated(false)
                            ->helperText('What customers see on the shop card.')
                            ->afterStateHydrated(function (Set $set, ?Product $record): void {
                                // On edit, seed from the persisted shop price so both boxes agree
                                // the moment the form opens. On create, leave it blank.
                                if ($record && $record->price_cents > 0) {
                                    $set('selling_incl_vat', $record->price_cents / 100);
                                }
                            })
                            ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                                if (blank($state)) {
                                    $set('selling_ex_vat_cents', null);

                                    return;
                                }

                                $inclCents = Money::toCents($state);
                                $exCents = VatPrice::exVatCents($inclCents);
                                $exRands = $exCents / 100;

                                $currentEx = $get('selling_ex_vat_cents');
                                $exAlreadyMatches = $currentEx !== null && $currentEx !== '' && (float) $currentEx === (float) $exRands;

                                if (! $exAlreadyMatches) {
                                    $set('selling_ex_vat_cents', $exRands);
                                }

                                // A typed inc-VAT price is exact by definition — kill the
                                // round-up toggle so we don't silently bump the shop price
                                // above what Dirk asked for.
                                if ((bool) $get('round_price_up')) {
                                    $set('round_price_up', false);
                                }
                            }),

                        Toggle::make('round_price_up')
                            ->label('Round the shop price up to the next rand')
                            ->live()
                            ->helperText('Only affects the ex-VAT direction. Disabled automatically when you type an inc-VAT price.')
                            ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                                // Toggle changed — recompute the inc-VAT sibling from the
                                // current ex-VAT value so the two boxes stay in sync.
                                $ex = $get('selling_ex_vat_cents');
                                if (blank($ex)) {
                                    return;
                                }

                                $exCents = Money::toCents($ex);
                                $inclCents = VatPrice::inclusiveCents($exCents, (bool) $state);
                                $set('selling_incl_vat', $inclCents / 100);
                            }),

                        Placeholder::make('vat_line')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->content(function (Get $get): string {
                                $ex = $get('selling_ex_vat_cents');
                                if (blank($ex)) {
                                    return 'Enter a selling price to see the VAT breakdown.';
                                }

                                $exCents = Money::toCents($ex);
                                $exactIncl = VatPrice::inclusiveCents($exCents, false);
                                $shopIncl = VatPrice::inclusiveCents($exCents, (bool) $get('round_price_up'));
                                $vat = $exactIncl - $exCents;

                                $line = 'VAT @ '.VatPrice::percentLabel().' — '.Money::format($vat);

                                if ((bool) $get('round_price_up') && $shopIncl !== $exactIncl) {
                                    $line .= ' · rounded up from '.Money::format($exactIncl);
                                }

                                return $line;
                            }),
                    ]),
                TextInput::make('stock_qty')
                    ->label('Stock quantity')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->helperText('Out-of-stock products are hidden from the shop.'),
                TextInput::make('reorder_level')
                    ->label('Reorder at')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->required()
                    ->helperText('The dashboard flags this product when stock drops to this number. 0 means only when it runs out.'),
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
