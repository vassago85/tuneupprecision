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
    $photo = $representative?->thumbnailUrl('web') ?? $representative?->thumbnailUrl(null);
@endphp
<div class="course discipline {{ $featured ? 'feat' : '' }} reveal" id="{{ $type?->slug }}">
  {{-- Four fixed regions so neighbouring cards share the same rows:
       intro (grows), specs, price, dates. --}}
  <div class="course-top">
    @if ($featured)
      <span class="tag">Most booked</span>
    @endif

    @if ($photo)
      <div class="course-photo">
        <img src="{{ $photo }}" alt="{{ $representative?->title ?? $type?->name }}" loading="lazy">
      </div>
    @endif

    <div class="evt-date">{{ $type?->name }}</div>
    @if ($representative?->level)
      <div class="lvl">{{ $representative->level }}</div>
    @endif
    <h3>{{ $representative?->title ?? $type?->name }}</h3>
    @if ($blurb)
      <div class="desc">{{ $blurb }}</div>
    @endif

    @if (! empty($type?->learnings))
      <div class="learn-block">
        <div class="learn-title">What you'll learn</div>
        <ul class="learn-list">
          @foreach ($type->learnings as $bullet)
            <li>{{ $bullet }}</li>
          @endforeach
        </ul>
      </div>
    @endif
  </div>

  <div class="course-specs">
    @if (! empty($specs))
      <x-site.dope-card :rows="$specs" />
    @endif
  </div>

  <div class="price">
    {{-- Always reserve the "From" line so the rand amounts share a baseline
         whether or not this card's price varies by date. --}}
    <s class="text-lead">{{ $priceIsFrom && $fromPriceCents > 0 ? 'From' : '' }}</s>
    <div class="price-main">
      @if ($fromPriceCents > 0)
        <b>{{ Money::format((int) $fromPriceCents, false) }}</b>
        <s>per shooter</s>
      @else
        <b>On request</b>
      @endif
    </div>
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
          <a href="{{ route('contact.create', ['subject' => 'Fully booked: '.$dataLabel]) }}" class="btn-mini waitlist">Enquire</a>
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
