<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingEftPaymentsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $pending = Payment::query()
            ->where('status', PaymentStatus::Pending)
            ->where('method', PaymentMethod::Eft);

        $count = (clone $pending)->count();
        $total = (int) (clone $pending)->sum('amount_cents');

        return [
            Stat::make('Pending EFT payments', (string) $count)
                ->description($count > 0 ? Money::format($total).' awaiting confirmation' : 'All caught up')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($count > 0 ? 'warning' : 'success'),
        ];
    }
}
