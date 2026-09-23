<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Actions\FulfillOrder;
use App\Enums\OrderStatus;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class FulfillOrderAction
{
    public static function make(): Action
    {
        return Action::make('fulfill')
            ->label('Mark as sent')
            ->icon('heroicon-o-truck')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Mark this order as sent')
            ->modalDescription('The customer is emailed that the order is with the courier.')
            ->visible(fn (Order $record): bool => $record->status === OrderStatus::Paid)
            ->action(function (Order $record): void {
                app(FulfillOrder::class)->handle($record);

                Notification::make()
                    ->title('Order marked as sent')
                    ->success()
                    ->send();
            });
    }
}
