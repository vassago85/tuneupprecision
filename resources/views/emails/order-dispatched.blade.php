<x-mail::message>
# Your order is on its way

Hi {{ $order->customer_name }},

**{{ $order->reference }}** has been handed to the courier.

**Deliver to:** {{ $order->deliveryAddress() }}

@foreach ($order->orderItems as $item)
- {{ $item->name_snapshot }} × {{ $item->qty }}
@endforeach

Thanks,<br>
Tune Up Precision
</x-mail::message>
