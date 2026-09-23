<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Models\Payment;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class ShopDeskWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $toPay = Order::query()->where('status', OrderStatus::Pending);
        $toPayCount = (clone $toPay)->count();
        $toPayTotal = (int) (clone $toPay)->sum(DB::raw('subtotal_cents + shipping_cents'));

        $toSend = Order::query()->where('status', OrderStatus::Paid)->count();

        $shopRevenue = (int) Payment::query()
            ->where('payable_type', Order::class)
            ->where('status', PaymentStatus::Paid)
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount_cents');

        return [
            Stat::make('To pay', (string) $toPayCount)
                ->description($toPayCount > 0 ? Money::format($toPayTotal).' waiting on EFT' : 'No shop orders waiting')
                ->descriptionIcon('heroicon-m-clock')
                ->color($toPayCount > 0 ? 'warning' : 'success')
                ->url(OrderResource::getUrl('index').'?tab=to-pay'),

            Stat::make('To send', (string) $toSend)
                ->description($toSend > 0 ? 'Paid, not with the courier yet' : 'Nothing waiting to go out')
                ->descriptionIcon('heroicon-m-truck')
                ->color($toSend > 0 ? 'info' : 'success')
                ->url(OrderResource::getUrl('index').'?tab=to-send'),

            Stat::make('Shop sales', Money::format($shopRevenue))
                ->description('Paid orders in '.now()->format('F'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary')
                ->url(OrderResource::getUrl('index').'?tab=all'),
        ];
    }
}
