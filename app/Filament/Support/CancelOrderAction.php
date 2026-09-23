<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Actions\CancelOrder;
use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class CancelOrderAction
{
    public static function make(): Action
    {
        return Action::make('cancelOrder')
            ->label('Cancel order')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Cancel this order')
            ->modalDescription('A paid order puts the stock back. This does not refund the EFT. A sent order cannot be cancelled here.')
            ->visible(fn (Order $record): bool => in_array($record->status, [OrderStatus::Pending, OrderStatus::Paid], true))
            ->action(function (Order $record): void {
                app(CancelOrder::class)->handle($record);

                Notification::make()
                    ->title('Order cancelled')
                    ->success()
                    ->send();
            });
    }
}
