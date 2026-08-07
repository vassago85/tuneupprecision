<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Support\Money;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockProductsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Low-stock products';

    /** Products at or below this quantity are considered low stock. */
    protected int $threshold = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Product::query()
                    ->where('is_active', true)
                    ->where('stock_qty', '<=', $this->threshold)
                    ->orderBy('stock_qty')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Product')
                    ->weight('bold'),
                TextColumn::make('category'),
                TextColumn::make('stock_qty')
                    ->label('In stock')
                    ->badge()
                    ->color(fn (int $state): string => $state <= 0 ? 'danger' : 'warning'),
                TextColumn::make('price_cents')
                    ->label('Price')
                    ->formatStateUsing(fn (int $state): string => Money::format($state)),
            ])
            ->emptyStateHeading('Stock levels are healthy')
            ->paginated(false);
    }
}
