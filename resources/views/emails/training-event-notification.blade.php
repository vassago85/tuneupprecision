<x-mail::message>
@if (filled($recipientName))
Hi {{ $recipientName }},
@endif

{!! nl2br(e($body)) !!}

@php
    $course = optional($event->courseTemplate)->title;
    $starts = optional($event->starts_on)->format('l, d M Y');
@endphp
@if ($course || $starts || $event->venue)
<x-mail::panel>
**{{ $course ?? 'Your training' }}**

@if ($starts)
- **Date:** {{ $starts }}
@endif
@if ($event->venue)
- **Venue:** {{ $event->venue }}
@endif
</x-mail::panel>
@endif

Thanks,<br>
Tune Up Long Range Precision Shooting
</x-mail::message>
