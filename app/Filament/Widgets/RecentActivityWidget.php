<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Enums\OrderStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\Booking;
use App\Models\Order;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Latest course bookings and shop orders in one feed.
 */
class RecentActivityWidget extends Widget
{
    private const LIMIT = 6;

    protected static bool $isLazy = false;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.recent-activity';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return ['entries' => $this->entries()];
    }

    /**
     * @return Collection<int, array{name: string, what: string, amount: string, status: BookingStatus|OrderStatus, at: Carbon, url: string}>
     */
    public function entries(): Collection
    {
        $bookings = Booking::query()
            ->with('trainingEvent.courseTemplate')
            ->latest()
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (Booking $booking): array => [
                'name' => $booking->customer_name,
                'what' => $booking->trainingEvent?->displayTitle() ?? 'Course booking',
                'amount' => $booking->amount,
                'status' => $booking->status,
                'at' => $booking->created_at,
                'url' => BookingResource::getUrl('edit', ['record' => $booking]),
            ]);

        $orders = Order::query()
            ->latest()
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (Order $order): array => [
                'name' => $order->customer_name,
                'what' => 'Shop order '.$order->reference,
                'amount' => $order->total,
                'status' => $order->status,
                'at' => $order->created_at,
                'url' => OrderResource::getUrl('view', ['record' => $order]),
            ]);

        return $bookings->concat($orders)
            ->sortByDesc('at')
            ->take(self::LIMIT)
            ->values();
    }
}
