<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\QuoteStatus;
use App\Models\Quote;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class QuotesOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $open = Quote::query()
            ->whereIn('status', [QuoteStatus::Draft, QuoteStatus::Sent])
            ->count();

        $quotedThisMonth = Quote::query()
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->where('status', '!=', QuoteStatus::Draft);

        $quotedValue = (int) (clone $quotedThisMonth)->sum('total_cents');
        $quotedCount = (clone $quotedThisMonth)->count();
        $converted = Quote::query()
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->where('status', QuoteStatus::Converted)
            ->count();

        $rate = $quotedCount > 0 ? round(($converted / $quotedCount) * 100, 1) : 0;

        return [
            Stat::make('Open quotes', (string) $open)
                ->description('Draft + sent')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),
            Stat::make('Quoted this month', Money::format($quotedValue))
                ->description(now()->format('F Y'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
            Stat::make('Conversion rate', $rate.'%')
                ->description($converted.' converted of '.$quotedCount.' issued')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($rate >= 25 ? 'success' : 'warning'),
        ];
    }
}
