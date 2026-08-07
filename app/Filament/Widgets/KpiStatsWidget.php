<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\TrainingEvent;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KpiStatsWidget extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $upcoming = TrainingEvent::query()->upcoming()->count();

        $bookingsThisMonth = Booking::query()
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $revenueThisMonth = (int) Payment::query()
            ->where('status', PaymentStatus::Paid)
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount_cents');

        $pendingPayments = Payment::query()
            ->where('status', PaymentStatus::Pending)
            ->where('method', PaymentMethod::Eft);
        $pendingCount = (clone $pendingPayments)->count();
        $pendingTotal = (int) (clone $pendingPayments)->sum('amount_cents');

        return [
            Stat::make('Upcoming events', (string) $upcoming)
                ->description('Scheduled dates ahead')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Bookings this month', (string) $bookingsThisMonth)
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Payments received', Money::format($revenueThisMonth))
                ->description('Confirmed this month')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Needs attention', (string) $pendingCount)
                ->description($pendingCount > 0
                    ? Money::format($pendingTotal).' awaiting EFT confirmation'
                    : 'Nothing pending')
                ->descriptionIcon($pendingCount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($pendingCount > 0 ? 'warning' : 'success'),
        ];
    }
}
