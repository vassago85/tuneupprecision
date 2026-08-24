@props([
    'type',
    'representative' => null,
    'events' => null,
    'fromPriceCents' => 0,
    'priceIsFrom' => false,
    'featured' => false,
])
@php
    use App\Support\Money;

    $events = $events ?? collect();
    $blurb = $representative?->blurb ?? $type?->blurb;

    $specs = collect($representative?->specs ?? [])->all();
@endphp
<div class="course discipline {{ $featured ? 'feat' : '' }} reveal" id="{{ $type?->slug }}">
  @if ($featured)
    <span class="tag">Most booked</span>
  @endif

  <div class="evt-date">{{ $type?->name }}</div>
  @if ($representative?->level)
    <div class="lvl">{{ $representative->level }}</div>
  @endif
  <h3>{{ $representative?->title ?? $type?->name }}</h3>
  @if ($blurb)
    <div class="desc">{{ $blurb }}</div>
  @endif

  @if (! empty($specs))
    <x-site.dope-card :rows="$specs" />
  @endif

  <div class="price">
    @if ($fromPriceCents > 0)
      @if ($priceIsFrom)<s class="text-lead">From</s>@endif
      <b>{{ Money::format((int) $fromPriceCents, false) }}</b>
      <s>per shooter</s>
    @else
      <b>On request</b>
    @endif
  </div>

  <div class="dates">
    @forelse ($events as $event)
      @php
        $isFull = $event->isFull();
        $seatsLeft = $event->seatsLeft();
        $dateLabel = $event->starts_on?->format('D d M Y');
        if ($event->ends_on && $event->ends_on->ne($event->starts_on)) {
            $dateLabel = $event->starts_on->format('D d M').' – '.$event->ends_on->format('D d M Y');
        }
        $courseTitle = $event->courseTemplate?->title;
        $dataLabel = trim(($courseTitle ? $courseTitle.' · ' : '').($event->starts_on?->format('d M Y') ?? ''));
      @endphp
      <div class="date-row {{ $isFull ? 'is-full' : '' }}" id="event-{{ $event->id }}">
        <div class="date-meta">
          <div class="d">{{ $dateLabel }}</div>
          <div class="s">
            @if ($isFull)
              Fully booked
            @else
              {{ $seatsLeft }} of {{ $event->capacity }} seats left
            @endif
            @if ($courseTitle && $courseTitle !== ($representative?->title))
              · {{ $courseTitle }}
            @endif
          </div>
        </div>
        @if ($isFull)
          <a href="{{ route('contact.create', ['subject' => 'Waitlist: '.$dataLabel]) }}" class="btn-mini waitlist">Waitlist</a>
        @else
          <a href="{{ route('contact.create', ['subject' => 'Book: '.$dataLabel]) }}"
             class="btn-mini book"
             data-course="{{ $dataLabel }}">Book</a>
        @endif
      </div>
    @empty
      <div class="date-empty">Dates coming soon — <a href="{{ route('contact.create', ['subject' => 'Next '.$type?->name.' date']) }}">message Dirk</a> to be first on the list.</div>
    @endforelse
  </div>
</div>
