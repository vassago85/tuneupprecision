<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockProductsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Product::query()->lowStock()->exists();
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Low stock')
            ->query(
                Product::query()
                    ->lowStock()
                    ->orderBy('stock_qty')
                    ->orderBy('name')
                    ->limit(5)
            )
            ->headerActions([
                Action::make('viewAll')
                    ->label('View all')
                    ->link()
                    ->url(ProductResource::getUrl('index').'?tab=low'),
            ])
            ->recordUrl(fn (Product $record): string => ProductResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('name')
                    ->label('Product')
                    ->description(fn (Product $record): ?string => $record->category)
                    ->weight('bold')
                    ->wrap(),
                TextColumn::make('stock_qty')
                    ->label('In stock')
                    ->badge()
                    ->color(fn (int $state): string => $state <= 0 ? 'danger' : 'warning'),
                TextColumn::make('reorder_level')
                    ->label('Reorder at')
                    ->color('gray'),
            ])
            ->paginated(false);
    }
}
