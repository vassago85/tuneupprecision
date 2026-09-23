<x-mail::message>
@if ($forCustomer)
# Order received

Hi {{ $order->customer_name }},

Thanks for the order. Pay by EFT and use **{{ $order->reference }}** as the payment reference. We dispatch once the payment reflects.

**Deliver to:** {{ $order->deliveryAddress() }}

@foreach ($order->orderItems as $item)
- {{ $item->name_snapshot }} × {{ $item->qty }} — {{ $item->lineTotal }}
@endforeach

- **Goods:** {{ $order->subtotal }}
- **Delivery:** {{ \App\Support\Money::format((int) $order->shipping_cents) }}
- **Total:** {{ $order->total }}
- **VAT included:** {{ \App\Support\Money::format(\App\Support\VatPrice::includedVatCents($order->totalCents())) }}

**EFT**

- **Bank:** {{ $eft['bank_name'] }}
- **Account name:** {{ $eft['account_name'] }}
- **Account number:** {{ $eft['account_number'] }}
- **Branch code:** {{ $eft['branch_code'] }}
- **Reference:** {{ $order->reference }}
@else
# New shop order

{{ $order->customer_name }} placed {{ $order->reference }}.

- **Email:** {{ $order->email }}
- **Phone:** {{ $order->phone }}
- **Deliver to:** {{ $order->deliveryAddress() }}
- **Total:** {{ $order->total }}

@foreach ($order->orderItems as $item)
- {{ $item->name_snapshot }} × {{ $item->qty }} — {{ $item->lineTotal }}
@endforeach

Payment is pending. Mark it paid in admin once the EFT reflects.
@endif

Thanks,<br>
Tune Up Precision
</x-mail::message>
