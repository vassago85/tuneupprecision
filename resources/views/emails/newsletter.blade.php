<x-mail::message>
@if (filled($recipientName))
Hi {{ $recipientName }},
@endif

{!! nl2br(e($body)) !!}

Thanks,<br>
Tune Up Long Range Precision Shooting

<x-slot:subcopy>
You're receiving this because you subscribed to the Tune Up Precision newsletter.
[Unsubscribe]({{ $unsubscribeUrl }}) at any time.
</x-slot:subcopy>
</x-mail::message>
