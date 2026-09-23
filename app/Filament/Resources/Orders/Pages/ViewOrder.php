<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Support\CancelOrderAction;
use App\Filament\Support\DownloadInvoiceAction;
use App\Filament\Support\FulfillOrderAction;
use App\Filament\Support\MarkAsPaidAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return (string) $this->getRecord()->reference;
    }

    protected function getHeaderActions(): array
    {
        return [
            MarkAsPaidAction::make(),
            FulfillOrderAction::make(),
            DownloadInvoiceAction::make(),
            CancelOrderAction::make(),
            EditAction::make(),
        ];
    }
}
