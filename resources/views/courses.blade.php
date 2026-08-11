<x-layouts.site title="Courses">

  {{-- ============ COURSE AGENDA ============ --}}
  <section>
    <div class="wrap">
      <div class="sec-head reveal">
        <span class="eyebrow">Upcoming course dates</span>
        <h2>Pick a date. Book your seat.</h2>
        <p>Each date is a full day at a private facility — on the line or at the bench, depending on the course. Bring your own rifle and ammo; targets and use of the ballistic and reloading kit are included.</p>
      </div>

      @if ($trainingTypes->isNotEmpty())
        <div class="type-filter reveal">
          <a href="{{ route('courses') }}" class="{{ $selectedType ? '' : 'active' }}">All training</a>
          @foreach ($trainingTypes as $type)
            <a href="{{ route('courses', ['type' => $type->slug]) }}" class="{{ $selectedType === $type->slug ? 'active' : '' }}">{{ $type->name }}</a>
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
            No upcoming {{ optional($trainingTypes->firstWhere('slug', $selectedType))->name ?? 'dates for this discipline' }} dates right now — <a href="{{ route('courses') }}">see all training</a> or message Dirk to be first on the list.
          @else
            New dates are being scheduled — message Dirk to be first on the list.
          @endif
        </div>
      @endforelse

      {{-- One-on-one coaching --}}
      <div class="private reveal">
        <div class="txt">
          <h3>One-on-one coaching</h3>
          <p>A full day built entirely around you and your rifle — load development, a problem you can't crack, or match prep for a specific stage. Quoted individually depending on what's required.</p>
        </div>
        <div class="p2">
          <div class="amt">On request <s>Quoted per day · scoped to what you need</s></div>
          <a href="mailto:hello@tuneupprecision.co.za?subject=One-on-one%20coaching" class="btn btn-primary book" data-course="One-on-one coaching">Enquire</a>
        </div>
      </div>
    </div>
  </section>

  <x-site.cta-band />

</x-layouts.site>
