<x-mail::message>
# Payment received

Hi {{ $order->customer_name }},

Your order **{{ $order->reference }}** is confirmed. The tax invoice is attached.

**Deliver to:** {{ $order->deliveryAddress() }}

@foreach ($order->orderItems as $item)
- {{ $item->name_snapshot }} × {{ $item->qty }} — {{ $item->lineTotal }}
@endforeach

- **Total paid:** {{ $order->total }}
- **VAT included:** {{ $vatIncluded }}

We'll hand it to the courier within {{ config('legal.shipping.dispatch_business_days', '3–5') }} business days.

Thanks,<br>
Tune Up Precision
</x-mail::message>
