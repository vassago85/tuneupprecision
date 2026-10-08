<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\QuoteStatus;
use App\Models\Booking;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Quote;
use App\Models\TrainingEvent;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Str;

class KpiStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|array|null $columns = ['default' => 1, 'sm' => 2, 'lg' => 4];

    protected function getStats(): array
    {
        return [
            $this->revenueStat(),
            $this->bookingsStat(),
            $this->seatsStat(),
            $this->openQuotesStat(),
        ];
    }

    private function revenueStat(): Stat
    {
        $paidByType = Payment::query()
            ->where('status', PaymentStatus::Paid)
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->selectRaw('payable_type, sum(amount_cents) as total')
            ->groupBy('payable_type')
            ->pluck('total', 'payable_type')
            ->map(fn ($total): int => (int) $total);

        $split = [
            'Training '.Money::format($paidByType->get(Booking::class, 0)),
            'Shop '.Money::format($paidByType->get(Order::class, 0)),
        ];

        if ($paidByType->get(Quote::class, 0) > 0) {
            $split[] = 'Builds '.Money::format($paidByType->get(Quote::class));
        }

        // Non-breaking spaces keep each "Shop R2 860.00" on one line when the card wraps.
        return Stat::make('Revenue this month', Money::format($paidByType->sum()))
            ->description(implode(' · ', str_replace(' ', "\u{00A0}", $split)));
    }

    private function bookingsStat(): Stat
    {
        $bookings = Booking::query()
            ->where('status', '!=', BookingStatus::Cancelled)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);

        $seats = (int) (clone $bookings)->sum('seats');

        return Stat::make('Bookings this month', (string) $bookings->count())
            ->description($seats.' '.Str::plural('seat', $seats).' in '.now()->format('F'));
    }

    /**
     * Same course dates the "Upcoming training" table lists, so the numbers agree.
     */
    private function seatsStat(): Stat
    {
        $events = TrainingEvent::query()
            ->onCoursesPage()
            ->whereDate('starts_on', '<=', now()->addDays(30)->toDateString())
            ->get(['id', 'capacity', 'seats_taken']);

        if ($events->isEmpty()) {
            return Stat::make('Seats filled, next 30 days', '—')
                ->description('No course dates in the next 30 days');
        }

        return Stat::make('Seats filled, next 30 days', $events->sum('seats_taken').' / '.$events->sum('capacity'))
            ->description('Across '.$events->count().' '.Str::plural('course date', $events->count()));
    }

    private function openQuotesStat(): Stat
    {
        $open = Quote::query()->whereIn('status', [QuoteStatus::Draft, QuoteStatus::Sent]);
        $count = (clone $open)->count();

        return Stat::make('Open quote value', Money::format((int) $open->sum('total_cents')))
            ->description($count > 0 ? $count.' '.Str::plural('quote', $count).' drafted or sent' : 'No open quotes');
    }
}
