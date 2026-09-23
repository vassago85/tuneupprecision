<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body{font-family:'DejaVu Sans',sans-serif;font-size:11px;color:#1a1a1a}
        .q-hd{border-bottom:2px solid #D45B2E;padding-bottom:9px;margin-bottom:14px}
        .q-hd .co{font-size:9px;letter-spacing:1px;color:#555;margin-top:5px;line-height:1.7}
        .q-meta{text-align:right;font-size:10px;line-height:1.75}
        .q-meta b{font-size:14px}
        .paid{display:inline-block;margin-top:6px;padding:3px 8px;background:#2C3E50;color:#fff;font-size:9px;letter-spacing:1px}
        .q-to{width:100%;margin-bottom:14px}
        .q-to td{width:50%;vertical-align:top;font-size:10px;line-height:1.7}
        .q-to h4{font-size:12px;margin:0 0 3px;border-bottom:1px solid #ddd;padding-bottom:2px}
        table.q{width:100%;border-collapse:collapse;font-size:10px}
        table.q th{background:#2C3E50;color:#fff;text-align:left;padding:5px 6px;font-size:8.5px;text-transform:uppercase}
        table.q td{border-bottom:1px solid #E4E4E0;padding:5px 6px;vertical-align:top}
        table.q td.r,table.q th.r{text-align:right}
        .q-tot{margin-top:10px;margin-left:auto;width:56%;font-size:10.5px}
        .q-tot div{display:table;width:100%;padding:3px 6px}
        .q-tot span{display:table-cell}
        .q-tot span:last-child{text-align:right}
        .q-tot .g{background:#2C3E50;color:#fff;font-size:14px;padding:7px 6px;margin-top:4px}
        .q-notes{margin-top:16px;font-size:8.6px;line-height:1.75;color:#444;border-top:1px solid #ddd;padding-top:8px}
    </style>
</head>
<body>
    @php
        $paidOn = $order->payment?->paid_at ?? $order->updated_at;
    @endphp
    <div class="q-hd">
        <table width="100%"><tr>
            <td>
                <h1 style="margin:0;font-size:26px">Tune Up Precision</h1>
                <div class="co">TAX INVOICE<br>
                    {{ collect([
                        $business['legal_phone'] ? 'TEL '.$business['legal_phone'] : null,
                        $business['legal_email'] ? strtoupper((string) $business['legal_email']) : null,
                    ])->filter()->implode(' · ') }}<br>
                    {{ collect([
                        $business['vat_no'] ? 'VAT NO. '.$business['vat_no'] : null,
                        $business['dealer_licence_no'] ? 'DEALER NO. '.$business['dealer_licence_no'] : null,
                        $business['registration_no'] ? 'REG '.$business['registration_no'] : null,
                    ])->filter()->implode(' · ') }}
                    @if ($business['physical_address'])
                        <br>{{ $business['physical_address'] }}
                    @endif
                </div>
            </td>
            <td class="q-meta">
                INVOICE<br><b>{{ $order->reference }}</b><br>
                DATE {{ $paidOn?->format('Y-m-d') }}<br>
                <span class="paid">PAID</span>
            </td>
        </tr></table>
    </div>

    <table class="q-to"><tr>
        <td>
            <h4>Bill to</h4>
            {{ $order->customer_name }}<br>
            @if ($order->phone){{ $order->phone }}<br>@endif
            {{ $order->email }}
        </td>
        <td>
            <h4>Deliver to</h4>
            @if ($order->address_line_1)
                {{ $order->address_line_1 }}<br>
                @if ($order->address_line_2){{ $order->address_line_2 }}<br>@endif
                {{ collect([$order->suburb, $order->city])->filter()->implode(', ') }}<br>
                {{ collect([$order->province, $order->postal_code])->filter()->implode(' ') }}
            @else
                {{ $order->deliveryAddress() ?: '—' }}
            @endif
        </td>
    </tr></table>

    <table class="q">
        <thead>
            <tr>
                <th>Item</th>
                <th class="r">Qty</th>
                <th class="r">Unit incl. VAT</th>
                <th class="r">Amount incl. VAT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->orderItems as $item)
                <tr>
                    <td>{{ $item->name_snapshot }}</td>
                    <td class="r">{{ $item->qty }}</td>
                    <td class="r">{{ \App\Support\Money::format((int) $item->price_cents_snapshot) }}</td>
                    <td class="r">{{ \App\Support\Money::format($item->lineTotalCents()) }}</td>
                </tr>
            @endforeach
            @if ((int) $order->shipping_cents > 0)
                <tr>
                    <td>Delivery</td>
                    <td class="r">1</td>
                    <td class="r">{{ \App\Support\Money::format((int) $order->shipping_cents) }}</td>
                    <td class="r">{{ \App\Support\Money::format((int) $order->shipping_cents) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="q-tot">
        <div><span>Goods incl. VAT</span><span>{{ \App\Support\Money::format((int) $order->subtotal_cents) }}</span></div>
        <div><span>Delivery</span><span>{{ (int) $order->shipping_cents === 0 ? 'Included' : \App\Support\Money::format((int) $order->shipping_cents) }}</span></div>
        <div><span>Excl. VAT</span><span>{{ \App\Support\Money::format($exVatCents) }}</span></div>
        <div><span>VAT @ {{ \App\Support\VatPrice::percentLabel() }}</span><span>{{ \App\Support\Money::format($vatCents) }}</span></div>
        <div class="g"><span>TOTAL PAID</span><span>{{ \App\Support\Money::format($order->totalCents()) }}</span></div>
    </div>

    <div class="q-notes">
        Prices are in ZAR and include VAT at {{ \App\Support\VatPrice::percentLabel() }}.
        Payment has been received. This invoice is the record of the sale.
        @if ($order->payment?->reference)
            Payment reference {{ $order->payment->reference }}.
        @endif
        E&amp;OE.
    </div>
</body>
</html>
