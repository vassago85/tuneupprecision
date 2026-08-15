<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body{font-family:'DejaVu Sans',sans-serif;font-size:11px;color:#1a1a1a}
        .q-hd{display:table;width:100%;border-bottom:2px solid #D45B2E;padding-bottom:9px;margin-bottom:14px}
        .q-hd .co{font-size:9px;letter-spacing:1px;color:#555;margin-top:5px;line-height:1.7}
        .q-meta{text-align:right;font-size:10px;line-height:1.75}
        .q-meta b{font-size:14px}
        .q-to{width:100%;margin-bottom:14px}
        .q-to td{width:50%;vertical-align:top;font-size:10px;line-height:1.7}
        .q-to h4{font-size:12px;margin:0 0 3px;border-bottom:1px solid #ddd;padding-bottom:2px}
        table.q{width:100%;border-collapse:collapse;font-size:10px}
        table.q th{background:#2C3E50;color:#fff;text-align:left;padding:5px 6px;font-size:8.5px;text-transform:uppercase}
        table.q td{border-bottom:1px solid #E4E4E0;padding:5px 6px;vertical-align:top}
        table.q td.r,table.q th.r{text-align:right}
        table.q tr.grp td{background:#F2F2EE;font-size:11px;text-transform:uppercase;letter-spacing:1px}
        .q-tot{margin-top:10px;margin-left:auto;width:56%;font-size:10.5px}
        .q-tot div{display:table;width:100%;padding:3px 6px}
        .q-tot span{display:table-cell}
        .q-tot span:last-child{text-align:right}
        .q-tot .g{background:#2C3E50;color:#fff;font-size:14px;padding:7px 6px;margin-top:4px}
        .q-notes{margin-top:16px;font-size:8.6px;line-height:1.75;color:#444;border-top:1px solid #ddd;padding-top:8px}
        .q-sig{margin-top:20px;width:100%}
        .q-sig td{width:50%;border-top:1px solid #999;padding-top:4px;font-size:9px}
    </style>
</head>
<body>
    <div class="q-hd">
        <table width="100%"><tr>
            <td>
                <h1 style="margin:0;font-size:26px">Tune Up Precision</h1>
                <div class="co">CUSTOM RIFLE BUILD QUOTATION<br>
                    TEL {{ $business['tel'] }} · {{ strtoupper($business['email'] ?? '') }}<br>
                    VAT NO. {{ $business['vat_number'] }} · DEALER NO. {{ $business['dealer_number'] }}
                </div>
            </td>
            <td class="q-meta">QUOTE<br><b>{{ $quote->reference }}</b><br>
                DATE {{ $quote->created_at?->format('Y-m-d') }}<br>
                VALID UNTIL {{ optional($quote->valid_until)->format('Y-m-d') ?: '—' }}
            </td>
        </tr></table>
    </div>

    <table class="q-to"><tr>
        <td>
            <h4>Quoted to</h4>
            {{ $quote->customer_name }}<br>
            {{ $quote->customer_phone }}<br>
            {{ $quote->customer_email }}<br>
            STATUS: {{ $quote->licence_status ?: '—' }}
        </td>
        <td>
            <h4>Build summary</h4>
            PLATFORM: {{ $quote->platform->getLabel() }}<br>
            @if ($quote->chambering)
                CHAMBERING: {{ $quote->chambering }} · {{ $quote->barrel_length }} · {{ $quote->barrel_twist }}<br>
            @endif
            LEAD TIME: {{ $quote->lead_time }}<br>
            LINE ITEMS: {{ $quote->lines->count() }}
        </td>
    </tr></table>

    <table class="q">
        <thead>
            <tr><th>Component / service</th><th class="r">Qty</th><th class="r">Unit incl.</th><th class="r">Amount incl.</th></tr>
        </thead>
        <tbody>
            @forelse ($lines as $group => $groupLines)
                <tr class="grp"><td colspan="4">{{ $group }}</td></tr>
                @foreach ($groupLines as $line)
                    <tr>
                        <td>
                            <b>{{ $line->brand }} {{ $line->description }}</b>
                            @if ($line->specs)
                                <br><span style="color:#777">{{ implode(' · ', $line->specs) }}</span>
                            @endif
                        </td>
                        <td class="r">{{ $line->quantity }}</td>
                        <td class="r">{{ $line->unit_price_cents === 0 ? '—' : \App\Support\Money::format((int) $line->unit_price_cents) }}</td>
                        <td class="r">{{ $line->line_total_cents === 0 ? 'INCLUDED' : \App\Support\Money::format((int) $line->line_total_cents) }}</td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="4">No components selected</td></tr>
            @endforelse
        </tbody>
    </table>

    @php
        $ex = (int) $quote->total_cents - (int) $quote->vat_amount_cents;
        $deposit = $quote->depositCents();
    @endphp
    <div class="q-tot">
        <div><span>Subtotal incl. VAT</span><span>{{ \App\Support\Money::format((int) $quote->subtotal_cents) }}</span></div>
        @if ($quote->discount_amount_cents > 0)
            <div><span>Discount</span><span>− {{ \App\Support\Money::format((int) $quote->discount_amount_cents) }}</span></div>
        @endif
        <div><span>Excl. VAT</span><span>{{ \App\Support\Money::format($ex) }}</span></div>
        <div><span>VAT @ 15%</span><span>{{ \App\Support\Money::format((int) $quote->vat_amount_cents) }}</span></div>
        <div class="g"><span>TOTAL DUE</span><span>{{ \App\Support\Money::format((int) $quote->total_cents) }}</span></div>
        <div><span>Deposit to commence ({{ $quote->deposit_percent }}%)</span><span>{{ \App\Support\Money::format($deposit) }}</span></div>
    </div>

    <div class="q-notes">
        <h4>Terms &amp; notes</h4>
        @if ($quote->notes)
            {!! nl2br(e($quote->notes)) !!}<br><br>
        @endif
        1. Prices are in ZAR and include VAT at 15%. Quote valid until the date shown, subject to stock availability and exchange-rate movement.<br>
        2. A {{ $quote->deposit_percent }}% deposit is required before ordering components; the balance is due on completion, prior to collection or transfer.<br>
        3. Lead time of {{ $quote->lead_time }} runs from receipt of deposit and confirmation of all component availability.<br>
        4. All firearm transfers are subject to a valid licence, SAPS approval and applicable dealer stock procedures. Tune Up Precision cannot release a firearm without the required authorisation.<br>
        5. Barrel life, load data and accuracy expectations are discussed at handover. Component manufacturer warranties apply; labour is warranted for 12 months.<br>
        6. Customer-supplied components are fitted at the customer's risk.<br>
        7. E&amp;OE.
    </div>

    <table class="q-sig"><tr>
        <td>CUSTOMER ACCEPTANCE — SIGNATURE &amp; DATE</td>
        <td>FOR TUNE UP PRECISION</td>
    </tr></table>
</body>
</html>
