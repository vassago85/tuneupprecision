<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\QuoteStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Quotes\QuoteResource;
use App\Filament\Resources\Testimonials\TestimonialResource;
use App\Models\Booking;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Quote;
use App\Models\Testimonial;
use App\Support\Money;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Only the things waiting on Dirk. Collapses to one "All caught up" line
 * when there is nothing to do.
 */
class NeedsActionWidget extends Widget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.needs-action';

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return ['items' => $this->items()];
    }

    /**
     * @return list<array{count: int, label: string, detail: ?string, url: string}>
     */
    public function items(): array
    {
        $pendingBookings = Booking::query()->where('status', BookingStatus::Pending);
        $bookingCount = (clone $pendingBookings)->count();

        $pendingOrders = Order::query()->where('status', OrderStatus::Pending);
        $orderCount = (clone $pendingOrders)->count();

        $pendingDeposits = Payment::query()
            ->where('payable_type', Quote::class)
            ->where('status', PaymentStatus::Pending);
        $depositCount = (clone $pendingDeposits)->count();

        $toSend = Order::query()->where('status', OrderStatus::Paid)->count();
        $draftQuotes = Quote::query()->where('status', QuoteStatus::Draft)->count();
        $testimonials = Testimonial::query()->where('is_approved', false)->count();

        $items = [
            [
                'count' => $bookingCount,
                'label' => Str::plural('Course booking', $bookingCount).' awaiting EFT',
                'detail' => $bookingCount > 0 ? Money::format((int) (clone $pendingBookings)->sum('amount_cents')) : null,
                'url' => BookingResource::getUrl('index'),
            ],
            [
                'count' => $orderCount,
                'label' => Str::plural('Shop order', $orderCount).' awaiting EFT',
                'detail' => $orderCount > 0 ? Money::format((int) (clone $pendingOrders)->sum(DB::raw('subtotal_cents + shipping_cents'))) : null,
                'url' => OrderResource::getUrl('index').'?tab=to-pay',
            ],
            [
                'count' => $depositCount,
                'label' => Str::plural('Build deposit', $depositCount).' awaiting EFT',
                'detail' => $depositCount > 0 ? Money::format((int) (clone $pendingDeposits)->sum('amount_cents')) : null,
                'url' => QuoteResource::getUrl('index'),
            ],
            [
                'count' => $toSend,
                'label' => Str::plural('Order', $toSend).' to send',
                'detail' => 'Paid, not with the courier yet',
                'url' => OrderResource::getUrl('index').'?tab=to-send',
            ],
            [
                'count' => $draftQuotes,
                'label' => Str::plural('Draft quote', $draftQuotes).' to send',
                'detail' => null,
                'url' => QuoteResource::getUrl('index'),
            ],
            [
                'count' => $testimonials,
                'label' => Str::plural('Testimonial', $testimonials).' to approve',
                'detail' => null,
                'url' => TestimonialResource::getUrl('index'),
            ],
        ];

        return array_values(array_filter($items, fn (array $item): bool => $item['count'] > 0));
    }
}
