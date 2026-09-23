<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->label('')
                    ->collection('images')
                    ->conversion('thumb')
                    ->circular(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->toggleable(),
                TextInputColumn::make('name')
                    ->label('Shop name')
                    ->searchable()
                    ->rules(['required', 'string', 'max:120']),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(48)
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('category')
                    ->searchable()
                    ->color('gray'),
                TextColumn::make('cost_cents')
                    ->label('Nett ex VAT')
                    ->formatStateUsing(fn (int $state): string => Money::format($state))
                    ->sortable(),
                TextInputColumn::make('selling_ex_vat')
                    ->label('Sell ex VAT')
                    ->type('number')
                    ->step(0.01)
                    ->prefix('R', true)
                    ->rules(['nullable', 'numeric', 'min:0'])
                    ->placeholder('0.00'),
                ToggleColumn::make('round_price_up')
                    ->label('Round up'),
                TextColumn::make('price_cents')
                    ->label('Shop incl. VAT')
                    ->formatStateUsing(function (int $state, Product $record): string {
                        if ($record->selling_ex_vat_cents === null && $state === 0) {
                            return '—';
                        }

                        return Money::format($state);
                    })
                    ->sortable(),
                TextColumn::make('stock_qty')
                    ->label('Stock')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 5 => 'warning',
                        default => 'success',
                    })
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Published'),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options(fn (): array => Product::query()
                        ->whereNotNull('category')
                        ->where('category', '!=', '')
                        ->distinct()
                        ->orderBy('category')
                        ->pluck('category', 'category')
                        ->all()),
            ])
            ->recordActions([
                Action::make('description')
                    ->label('Description')
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->modalHeading('Short description')
                    ->modalSubmitActionLabel('Save')
                    ->fillForm(fn (Product $record): array => [
                        'description' => $record->description,
                    ])
                    ->schema([
                        Textarea::make('description')
                            ->label('Short description')
                            ->rows(3)
                            ->maxLength(180)
                            ->helperText('Optional. Shown under the shop name. Leave blank to show the name only.'),
                    ])
                    ->action(function (Product $record, array $data): void {
                        $record->description = $data['description'] ?? null;
                        $record->save();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
