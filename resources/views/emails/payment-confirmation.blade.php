{{-- TODO: Stub email body. Replace with branded template in a later commit. --}}
<x-mail::message>
# Payment received

Thanks, {{ $payable->customer_name }} — we've confirmed your payment.

@if ($isBooking)
Your seat on **{{ optional($payable->trainingEvent->courseTemplate)->title }}** is locked in.

- **Reference:** {{ $payable->reference }}
- **Date:** {{ optional($payable->trainingEvent->starts_on)->format('d M Y') }}
- **Amount:** {{ $payable->amount }}
@elseif ($isOrder)
Your order is being prepared.

- **Reference:** {{ $payable->reference }}
- **Total:** {{ $payable->subtotal }}
@elseif ($isQuote)
We've received the deposit on your rifle build.

- **Reference:** {{ $payable->reference }}
- **Deposit:** {{ $payable->payment?->amount }}
- **Build total:** {{ $payable->total }}
@endif

We'll be in touch by WhatsApp with the details.

Thanks,<br>
Tune Up Long Range Precision Shooting
</x-mail::message>
