@php
    $event = $getRecord();
    $capacity = max(1, (int) $event->capacity);
    $percent = (int) round(min((int) $event->seats_taken, $capacity) / $capacity * 100);
    $left = $event->seatsLeft();
    $tone = match (true) {
        $event->isFull() => 'full',
        $left <= 2 => 'almost',
        default => 'open',
    };
@endphp

<div class="tu-fill" title="{{ $left }} {{ str('seat')->plural($left) }} left">
    <div class="tu-fill-track">
        <div class="tu-fill-bar tu-fill-{{ $tone }}" style="width: {{ $percent }}%"></div>
    </div>
    <span class="tu-fill-label">{{ (int) $event->seats_taken }} / {{ (int) $event->capacity }}</span>
</div>
