<x-layouts.site title="Calendar">

  {{-- ============ HEADER ============ --}}
  <section>
    <div class="wrap">
      <div class="sec-head reveal">
        <span class="eyebrow">Course calendar</span>
        <h2>Every date on the line.</h2>
        <p>Each date is a full day at a private facility — on the line or at the bench, depending on the course. Bring your own rifle and ammo; targets and use of the ballistic and reloading kit are included.</p>
      </div>

      @if ($trainingTypes->isNotEmpty())
        <div class="type-filter reveal">
          <a href="{{ route('calendar') }}" class="{{ $selectedType ? '' : 'active' }}">All training</a>
          @foreach ($trainingTypes as $type)
            <a href="{{ route('calendar', ['type' => $type->slug]) }}" class="{{ $selectedType === $type->slug ? 'active' : '' }}">{{ $type->name }}</a>
          @endforeach
        </div>
      @endif

      @forelse ($eventsByMonth as $month => $events)
        <div class="month-head reveal">
          <h3>{{ $month }}</h3>
          <span class="rule"></span>
        </div>
        <div class="courses">
          @foreach ($events as $event)
            <x-training.event-card
              :event="$event"
              :featured="$event->courseTemplate?->slug === 'applied-long-range'"
            />
          @endforeach
        </div>
      @empty
        <div class="schedule-empty reveal">
          @if ($selectedType)
            No upcoming {{ optional($trainingTypes->firstWhere('slug', $selectedType))->name ?? 'dates for this discipline' }} dates right now — <a href="{{ route('calendar') }}">see all training</a> or message Dirk to be first on the list.
          @else
            New dates are being scheduled — message Dirk to be first on the list.
          @endif
        </div>
      @endforelse
    </div>
  </section>

  <x-site.cta-band />

</x-layouts.site>
