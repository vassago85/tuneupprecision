<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'all';
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'shop' => Tab::make('On the shop')
                ->badge(fn (): ?string => self::countBadge(
                    Product::query()->where('is_active', true)->where('price_cents', '>', 0),
                ))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('is_active', true)->where('price_cents', '>', 0)),
            'unpublished' => Tab::make('Not published')
                ->badge(fn (): ?string => self::countBadge(
                    Product::query()->where('is_active', false),
                ))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('is_active', false)),
            'unpriced' => Tab::make('Needs a price')
                ->badge(fn (): ?string => self::countBadge(
                    Product::query()->whereNull('selling_ex_vat_cents')->where('price_cents', 0),
                ))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereNull('selling_ex_vat_cents')->where('price_cents', 0)),
            'low' => Tab::make('Low stock')
                ->badge(fn (): ?string => self::countBadge(
                    Product::query()->where('is_active', true)->where('stock_qty', '<=', 5),
                ))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('is_active', true)->where('stock_qty', '<=', 5)),
        ];
    }

    private static function countBadge(Builder $query): ?string
    {
        $count = $query->count();

        return $count > 0 ? (string) $count : null;
    }
}
