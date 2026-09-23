<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Shop\OrderInvoice;
use Filament\Actions\Action;

class DownloadInvoiceAction
{
    public static function make(): Action
    {
        return Action::make('downloadInvoice')
            ->label('Download invoice')
            ->icon('heroicon-o-arrow-down-tray')
            ->visible(fn (Order $record): bool => in_array($record->status, [OrderStatus::Paid, OrderStatus::Fulfilled], true))
            ->action(fn (Order $record) => app(OrderInvoice::class)->download($record));
    }
}
