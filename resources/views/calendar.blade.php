<x-layouts.site title="Calendar">

  {{-- ============ MONTH CALENDAR ============ --}}
  <section
    x-data="{
      payload: {{ Illuminate\Support\Js::from($eventsPayload) }},
      current: null,
      open(id) {
        this.current = this.payload[id] || null;
        if (this.current) document.body.style.overflow = 'hidden';
      },
      close() {
        this.current = null;
        document.body.style.overflow = '';
      },
    }"
  >
    <div class="wrap">
      <div class="sec-head reveal">
        <span class="eyebrow">Event calendar</span>
        <h2>Every training day, one grid.</h2>
        <p>Browse upcoming dates month-by-month. Click any event chip for the details.</p>
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
                $tooltip = trim($title.($discipline ? ' · '.$discipline : '').($isComp && $event->dirk_role ? ' · '.$event->dirk_role : ''));
              @endphp
              <button type="button"
                      class="cal-evt {{ $isComp ? 'comp' : '' }}"
                      @click="open({{ $event->id }})"
                      title="{{ $tooltip }}">
                {{ $title }}
              </button>
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

    {{-- ============ EVENT MODAL ============ --}}
    <div class="modal-back"
         x-show="current"
         x-cloak
         x-transition.opacity
         @click.self="close()"
         @keydown.escape.window="close()"
         role="dialog"
         aria-modal="true">
      <div class="modal" x-show="current" x-transition>
        <button type="button" class="modal-close" @click="close()" aria-label="Close">&times;</button>
        <template x-if="current">
          <div>
            <span class="eyebrow"
                  x-text="current.kind === 'competition'
                    ? ('Competition' + (current.dirk_role ? ' · ' + current.dirk_role : ''))
                    : ((current.discipline ? current.discipline + ' · ' : '') + (current.level || 'Training'))"></span>
            <h3 x-text="current.title"></h3>

            <div class="modal-meta">
              <div><span>Date</span><b x-text="current.date_label"></b></div>
              <div><span>Venue</span><b x-text="current.venue"></b></div>
              <template x-if="current.price">
                <div><span x-text="current.price_note"></span><b x-text="current.price"></b></div>
              </template>
              <template x-if="current.seats_note">
                <div><span>Seats</span><b x-text="current.seats_note"></b></div>
              </template>
            </div>

            <p x-show="current.blurb" x-text="current.blurb"></p>

            <a class="btn btn-primary"
               :href="current.action_href"
               :target="current.action_external ? '_blank' : null"
               :rel="current.action_external ? 'noopener noreferrer' : null"
               x-text="current.action_label"></a>
          </div>
        </template>
      </div>
    </div>
  </section>

  <x-site.cta-band />

</x-layouts.site>
