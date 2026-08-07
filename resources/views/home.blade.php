<x-layouts.site>

  {{-- ============ HERO ============ --}}
  <span id="top"></span>
  <section class="hero">
    <div class="wrap hero-grid">
      <div class="hero-copy reveal">
        <span class="eyebrow">Precision rifle instruction · South Africa</span>
        <h1>Dial in<br>your <span class="cop">distance.</span></h1>
        <p class="lead">Small-squad precision coaching — long range shooting and handloading — that turns guesswork into a repeatable process, from your first zero to a load and wind call you can trust.</p>
        <div class="hero-cta">
          <a href="{{ url('/#courses') }}" class="btn btn-primary">Book a course
            <svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </a>
          <a href="{{ url('/#shop') }}" class="btn btn-ghost">Browse the shop</a>
        </div>
        <div class="hero-data">
          <div class="cell"><div class="k">Discipline</div><div class="v">PRS · ELR</div></div>
          <div class="cell"><div class="k">Squad size</div><div class="v">Max 6</div></div>
        </div>
      </div>
      <div class="hero-badge reveal">
        <img class="brand-emblem" src="{{ asset('images/logo.png') }}" alt="Tune Up — Long Range Precision Shooting" width="460" height="460">
      </div>
    </div>
  </section>

  <x-site.reticle-divider />

  {{-- ============ VALUES ============ --}}
  <section id="training">
    <div class="wrap">
      <div class="sec-head reveal">
        <span class="eyebrow">Why train here</span>
        <h2>Fundamentals first. Data always.</h2>
        <p>No magic, no gimmicks — just the disciplined process good shooters and reloaders actually run, taught step by step.</p>
      </div>
      <div class="values">
        <div class="val reveal">
          <div class="ic"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/><path d="M12 1v4M12 19v4M1 12h4M19 12h4"/></svg></div>
          <h3>Watched every round</h3>
          <p>Six shooters, one instructor on glass. Your misses get called and corrected in real time.</p>
        </div>
        <div class="val reveal">
          <div class="ic"><svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 14l3-4 3 3 4-6"/></svg></div>
          <h3>Real ballistic data</h3>
          <p>Chrono, Kestrel and Applied Ballistics on the line. You leave with a truing DOPE, not a printout.</p>
        </div>
        <div class="val reveal">
          <div class="ic"><svg viewBox="0 0 24 24"><path d="M3 20l6-9 4 5 3-4 5 8z"/><circle cx="8" cy="6" r="2"/></svg></div>
          <h3>Steel at distance</h3>
          <p>Full-value, half-value, transitions and positional stages out to the far plates. Wind you can hear ring.</p>
        </div>
        <div class="val reveal">
          <div class="ic"><svg viewBox="0 0 24 24"><path d="M4 20l7-7"/><path d="M14 3l7 7-4 1-4 4-1 4-7-7 4-1 4-4z"/></svg></div>
          <h3>Your own rifle</h3>
          <p>Whatever you compete or hunt with — we build the load and the process around your kit, not ours.</p>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ COURSE DATES (agenda) ============ --}}
  <section id="courses">
    <div class="wrap">
      <div class="sec-head reveal">
        <span class="eyebrow">Upcoming course dates</span>
        <h2>Pick a date. Book your seat.</h2>
        <p>Each date is a full day at a private facility — on the line or at the bench, depending on the course. Bring your own rifle and ammo; targets and use of the ballistic and reloading kit are included.</p>
      </div>

      @if ($trainingTypes->isNotEmpty())
        <div class="type-filter reveal">
          <a href="{{ url('/') }}#courses" class="{{ $selectedType ? '' : 'active' }}">All training</a>
          @foreach ($trainingTypes as $type)
            <a href="{{ url('/?type='.$type->slug) }}#courses" class="{{ $selectedType === $type->slug ? 'active' : '' }}">{{ $type->name }}</a>
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
            No upcoming {{ optional($trainingTypes->firstWhere('slug', $selectedType))->name ?? 'dates for this discipline' }} dates right now — <a href="{{ url('/') }}#courses">see all training</a> or message Dirk to be first on the list.
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
          <a href="{{ url('/#courses') }}" class="btn btn-primary book" data-course="One-on-one coaching">Enquire</a>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ ABOUT ============ --}}
  <section id="about">
    <div class="wrap about-grid">
      <div class="about-photo reveal">
        <img class="photo" src="{{ asset('images/dirk.png') }}" alt="Dirk shooting long range prone off a rest" loading="lazy">
        <div class="frame"></div>
      </div>
      <div class="about-copy reveal">
        <span class="eyebrow">Your instructor</span>
        <h2>Meet Dirk.</h2>
        <p>Tune Up runs on one idea: long range precision — shooting and reloading alike — is a process you can learn, not a talent you're born with. Dirk has spent years on the line as a competitor and at the bench as a reloader, and he coaches the way he works — methodically, with the data to back every call.</p>
        <p>You'll leave with a rifle and a load you trust, a DOPE you built yourself, and the confidence to make the shot when it counts.</p>
        <div class="creds">
          <div class="cred"><div class="n">10+</div><div class="l">Years on the line</div></div>
          <div class="cred"><div class="n">6</div><div class="l">Shooters per course</div></div>
          <div class="cred"><div class="n">Sub-MOA</div><div class="l">Loads built to</div></div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ PROCESS ============ --}}
  <section id="process">
    <div class="wrap">
      <div class="sec-head reveal">
        <span class="eyebrow">How a range day runs</span>
        <h2>Five stations. One process.</h2>
        <p>This is how a shooting day runs — the same sequence a good shooter follows under their own steam. Reloading days follow their own bench process; every course is built around a repeatable method.</p>
      </div>
      <div class="steps">
        <div class="step reveal"><div class="no">01</div><h4>Zero &amp; confirm</h4><p>Set a true zero and confirm your rifle and ammo are honest before anything else.</p></div>
        <div class="step reveal"><div class="no">02</div><h4>Build data</h4><p>Chrono, solver and a live truing pass so your dope matches the real world.</p></div>
        <div class="step reveal"><div class="no">03</div><h4>Read wind</h4><p>Learn to see, bracket and call the wind — the skill that separates hits from misses.</p></div>
        <div class="step reveal"><div class="no">04</div><h4>Engage steel</h4><p>Apply the solution on distant plates, then add position and time pressure.</p></div>
        <div class="step reveal"><div class="no">05</div><h4>Debrief</h4><p>Every shooter leaves with written data and the three things to work on next.</p></div>
      </div>
    </div>
  </section>

  {{-- ============ SHOP ============ --}}
  <section id="shop">
    <div class="wrap">
      <div class="shop-top">
        <div class="sec-head reveal">
          <span class="eyebrow">The kit shop</span>
          <h2>Gear that earns its place.</h2>
          <p>Merch and range essentials, shipped countrywide. More stock added between intakes.</p>
        </div>
        <a href="{{ url('/#shop') }}" class="btn btn-ghost reveal">View full shop
          <svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
      </div>
      <div class="shop">
        @forelse ($products as $product)
          <x-shop.product-card :product="$product" />
        @empty
          <p class="mono" style="color:var(--muted)">New stock lands between intakes — check back soon.</p>
        @endforelse
      </div>
    </div>
  </section>

  {{-- ============ FINAL CTA ============ --}}
  <x-site.cta-band />

</x-layouts.site>
