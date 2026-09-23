<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'open' => Tab::make('Open')
                ->badge(fn (): ?string => self::countBadge([OrderStatus::Pending, OrderStatus::Paid]))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('status', [OrderStatus::Pending, OrderStatus::Paid])),
            'to-pay' => Tab::make('To pay')
                ->badge(fn (): ?string => self::countBadge([OrderStatus::Pending]))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', OrderStatus::Pending)),
            'to-send' => Tab::make('To send')
                ->badge(fn (): ?string => self::countBadge([OrderStatus::Paid]))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', OrderStatus::Paid)),
            'sent' => Tab::make('Sent')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', OrderStatus::Fulfilled)),
            'all' => Tab::make('All'),
        ];
    }

    /**
     * @param  list<OrderStatus>  $statuses
     */
    private static function countBadge(array $statuses): ?string
    {
        $count = Order::query()->whereIn('status', $statuses)->count();

        return $count > 0 ? (string) $count : null;
    }
}
