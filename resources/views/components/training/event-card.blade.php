@props([
    'event',
    'featured' => false,
])
@php
    use App\Support\Money;

    $template = $event->courseTemplate;
    $isFull = $event->isFull();
    $seatsLeft = $event->seatsLeft();

    // Date label — single day or a range if ends_on is set.
    $dateLabel = $event->starts_on?->format('D d M Y');
    if ($event->ends_on && $event->ends_on->ne($event->starts_on)) {
        $dateLabel = $event->starts_on->format('D d M').' – '.$event->ends_on->format('D d M Y');
    }

    // DOPE spec block = template specs, plus venue and live seats-left.
    $specs = collect($template?->specs ?? [])
        ->put('Venue', $event->venue)
        ->put('Seats left', $isFull ? 'Fully booked' : (string) $seatsLeft.' of '.$event->capacity)
        ->all();
@endphp
<div class="course {{ $featured ? 'feat' : '' }} reveal">
  @if ($featured)
    <span class="tag">Most booked</span>
  @endif
  <div class="evt-date">{{ $dateLabel }}</div>
  @php
    $meta = array_filter([$template?->trainingType?->name, $template?->level]);
  @endphp
  @if ($meta)
    <div class="lvl">{{ implode(' · ', $meta) }}</div>
  @endif
  <h3>{{ $template?->title }}</h3>
  <div class="desc">{{ $template?->blurb }}</div>

  <x-site.dope-card :rows="$specs" />

  <div class="price"><b>{{ Money::format($event->effectivePriceCents(), false) }}</b><s>per shooter</s></div>

  @if ($isFull)
    <button class="btn fully" type="button" disabled aria-disabled="true">Fully booked</button>
  @else
    <a href="{{ url('/#courses') }}"
       class="btn {{ $featured ? 'btn-primary' : 'btn-dark' }} book"
       data-course="{{ $template?->title }} · {{ $event->starts_on?->format('d M Y') }}">Book this date</a>
  @endif
</div>
