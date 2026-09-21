<x-mail::message>
Hi {{ $booking->customer_name }},

Thanks for training with Tune Up Precision — hope you got what you came for.

If you have a minute, a short review from you helps other shooters know what to expect. Anything honest is useful: what worked, what surprised you, what you took away.

<x-mail::button :url="$signedUrl">
Leave a quick testimonial
</x-mail::button>

@php
    $course = optional($event?->courseTemplate)->title ?? optional($event)->displayTitle();
    $starts = optional($event?->starts_on)->format('l, d M Y');
@endphp
@if ($course || $starts || $event?->venue)
<x-mail::panel>
**{{ $course ?? 'Your training day' }}**

@if ($starts)
- **Date:** {{ $starts }}
@endif
@if ($event?->venue)
- **Venue:** {{ $event->venue }}
@endif
</x-mail::panel>
@endif

Prefer WhatsApp? Forward this link to yourself and open it on your phone — same one-tap form:

{{ $signedUrl }}

Every submission is read by Dirk before it goes live on the site.

Thanks,<br>
Tune Up Long Range Precision Shooting
</x-mail::message>
