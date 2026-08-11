<x-layouts.site title="Calendar">

  {{-- ============ MONTH CALENDAR ============ --}}
  <section>
    <div class="wrap">
      <div class="sec-head reveal">
        <span class="eyebrow">Event calendar</span>
        <h2>Every training day, one grid.</h2>
        <p>Browse upcoming training dates month-by-month. Click a date to jump to the course list, or use the arrows to move between months.</p>
      </div>

      <div class="cal-head reveal">
        <div class="cal-nav">
          <a class="cal-arrow" href="{{ route('calendar', ['month' => $prevMonth]) }}" aria-label="Previous month">
            <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="M15 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2"/></svg>
            <span>Prev</span>
          </a>
          <h2>{{ $month->format('F Y') }}</h2>
          <a class="cal-arrow" href="{{ route('calendar', ['month' => $nextMonth]) }}" aria-label="Next month">
            <span>Next</span>
            <svg viewBox="0 0 24 24" width="14" height="14" aria-hidden="true"><path d="M9 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2"/></svg>
          </a>
        </div>
      </div>

      <div class="cal-grid reveal">
        <div class="cal-dow">Mon</div>
        <div class="cal-dow">Tue</div>
        <div class="cal-dow">Wed</div>
        <div class="cal-dow">Thu</div>
        <div class="cal-dow">Fri</div>
        <div class="cal-dow">Sat</div>
        <div class="cal-dow">Sun</div>

        @foreach ($days as $day)
          <div class="cal-cell {{ $day['inMonth'] ? '' : 'out' }} {{ $day['isToday'] ? 'today' : '' }}">
            <div class="cal-date">{{ $day['date']->format('j') }}</div>
            @foreach ($day['events'] as $event)
              @php
                $isComp = $event->isCompetition();
                $title = $event->displayTitle();
                $discipline = $event->disciplineName();
                $href = $isComp
                  ? ($event->external_url ?? route('calendar'))
                  : route('courses');
                $tooltip = trim($title.($discipline ? ' · '.$discipline : '').($isComp && $event->dirk_role ? ' · '.$event->dirk_role : ''));
              @endphp
              <a class="cal-evt {{ $isComp ? 'comp' : '' }}"
                 href="{{ $href }}"
                 @if ($isComp && $event->external_url) target="_blank" rel="noopener noreferrer" @endif
                 title="{{ $tooltip }}">
                {{ $title }}
              </a>
            @endforeach
          </div>
        @endforeach
      </div>

      <div class="cal-legend reveal">
        <span class="k"><span class="sw"></span>Training</span>
        <span class="k"><span class="sw comp"></span>Competition · Dirk attending</span>
      </div>

      <div class="cal-foot reveal">
        <a href="{{ route('courses') }}" class="btn btn-ghost">See all upcoming dates
          <svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
      </div>
    </div>
  </section>

  <x-site.cta-band />

</x-layouts.site>
