<?php

declare(strict_types=1);

namespace App\Shop;

use App\Models\Order;
use App\Support\LegalIdentity;
use App\Support\VatPrice;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class OrderInvoice
{
    public function pdf(Order $order): \Barryvdh\DomPDF\PDF
    {
        $order->loadMissing('orderItems', 'payment');

        return Pdf::loadView('shop.invoice', $this->viewData($order))
            ->setPaper('a4');
    }

    public function download(Order $order): StreamedResponse
    {
        $pdf = $this->pdf($order);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $order->reference.'-invoice.pdf',
            ['Content-Type' => 'application/pdf'],
        );
    }

    /**
     * @return array{order: Order, business: array<string, mixed>, vatCents: int, exVatCents: int}
     */
    public function viewData(Order $order): array
    {
        $total = $order->totalCents();
        $vat = VatPrice::includedVatCents($total);

        return [
            'order' => $order,
            'business' => LegalIdentity::effective(),
            'vatCents' => $vat,
            'exVatCents' => $total - $vat,
        ];
    }
}
