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
          <a href="{{ route('courses') }}" class="btn btn-primary">Book a course
            <svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </a>
          <a href="{{ route('shop') }}" class="btn btn-ghost">Browse the shop</a>
        </div>
        <div class="hero-data">
          <div class="cell"><div class="k">We teach</div><div class="v">PRS · ELR · Reloading</div></div>
          <div class="cell"><div class="k">Squad size</div><div class="v">Max 6</div></div>
        </div>
      </div>
      <div class="hero-badge reveal">
        <img class="brand-emblem" src="{{ asset('images/logo.png') }}?v={{ @filemtime(public_path('images/logo.png')) ?: '2' }}" alt="Tune Up — Long Range Precision Training" width="658" height="557">
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

  {{-- ============ NEXT EVENT ============ --}}
  <section id="next-event">
    <div class="wrap">
      <div class="sec-head reveal">
        <span class="eyebrow">Next event</span>
        <h2>The next date on the line.</h2>
        <p>Here's the next scheduled training day. Full schedule of every upcoming date lives on the calendar.</p>
      </div>

      @if ($nextEvent)
        <div class="courses">
          <x-training.event-card :event="$nextEvent" :featured="true" />
        </div>
        <div class="reveal" style="margin-top:22px">
          <a href="{{ route('calendar') }}" class="btn btn-ghost">View full calendar
            <svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </a>
        </div>
      @else
        <div class="schedule-empty reveal">
          New dates are being scheduled — message Dirk to be first on the list.
        </div>
      @endif
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
        <div class="cred-line">
          <span>Founder &amp; first Chairperson, Pretoria Precision Rifle Club</span>
          <span>Co-founder, Royal Flush Steel Challenge</span>
          <span>SAPRF board</span>
          <span>Match director</span>
        </div>
        <p>Tune Up runs on one idea: long range precision — shooting and reloading alike — is a process you can learn, not a talent you're born with. Dirk coaches from the same process he runs on the line and at the bench: methodically, with the data to back every call.</p>
        <p>You'll leave with a rifle and a load you trust, a DOPE you built yourself, and the confidence to make the shot when it counts.</p>
        <div class="creds">
          <div class="cred"><div class="n">10+</div><div class="l">Years on the line</div></div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ PROCESS ============ --}}
  <section id="process">
    <div class="wrap" x-data="{ tab: 'prs' }">
      <div class="sec-head reveal">
        <span class="eyebrow">How a day runs</span>
        <h2>One method. Every discipline.</h2>
        <p>Every Tune Up day runs on a repeatable process — on the line or at the bench. Pick a discipline to see how the day is built.</p>
      </div>

      <div class="proc-tabbar reveal" role="tablist">
        <button type="button" role="tab" :class="{ active: tab === 'prs' }" @click="tab = 'prs'">PRS</button>
        <button type="button" role="tab" :class="{ active: tab === 'elr' }" @click="tab = 'elr'">ELR</button>
        <button type="button" role="tab" :class="{ active: tab === 'reloading' }" @click="tab = 'reloading'">Reloading</button>
      </div>

      {{-- PRS --}}
      <div x-show="tab === 'prs'" x-cloak>
        <p class="proc-meta">Private range · positional stages against the clock</p>
        <div class="steps">
          <div class="step"><div class="no">01</div><h3>Zero &amp; gear check</h3><p>Confirm zero and set the rifle, bag and bipod up for positional work.</p></div>
          <div class="step"><div class="no">02</div><h3>Build positions</h3><p>Get stable off barricades, tank traps and improvised support.</p></div>
          <div class="step"><div class="no">03</div><h3>Plan the stage</h3><p>Read a stage, build a plan and sequence your targets before the beep.</p></div>
          <div class="step"><div class="no">04</div><h3>Run the clock</h3><p>Engage multiple targets under time, transitioning between positions.</p></div>
          <div class="step"><div class="no">05</div><h3>Debrief</h3><p>Review each run and the fixes that buy back the most points.</p></div>
        </div>
      </div>

      {{-- ELR --}}
      <div x-show="tab === 'elr'" x-cloak>
        <p class="proc-meta">Private range · known distance out to the far steel</p>
        <div class="steps">
          <div class="step"><div class="no">01</div><h3>Zero &amp; confirm</h3><p>Set a true zero and confirm your rifle and ammo are honest before anything else.</p></div>
          <div class="step"><div class="no">02</div><h3>Build data</h3><p>Chrono, solver and a live truing pass so your DOPE matches the real world.</p></div>
          <div class="step"><div class="no">03</div><h3>Read wind</h3><p>Learn to see, bracket and call the wind — the skill that separates hits from misses.</p></div>
          <div class="step"><div class="no">04</div><h3>Engage steel</h3><p>Apply the solution on distant plates, stretching out to the far targets.</p></div>
          <div class="step"><div class="no">05</div><h3>Debrief</h3><p>Leave with written data and the three things to work on next.</p></div>
        </div>
      </div>

      {{-- Reloading --}}
      <div x-show="tab === 'reloading'" x-cloak>
        <p class="proc-meta">Dedicated reloading room · class-based, all equipment provided</p>
        <div class="steps">
          <div class="step"><div class="no">01</div><h3>Deprime &amp; anneal</h3><p>Punch primers first — so cleaning reaches the primer pockets, and so no live primer is in the case when heat goes on. Annealing the neck and shoulder restores even neck tension and stretches case life.</p></div>
          <div class="step"><div class="no">02</div><h3>Clean &amp; case prep</h3><p>Clean, then size, trim to length and chamfer. Uniform, correctly-trimmed brass is what keeps chambering, neck tension and pressure consistent shot to shot.</p></div>
          <div class="step"><div class="no">03</div><h3>Prime</h3><p>Small vs large rifle, standard vs magnum — matching the primer to the job, then seating it fully and consistently below flush. High or inconsistent primers mean misfires and safety risk.</p></div>
          <div class="step"><div class="no">04</div><h3>Powder</h3><p>Matching powder type and burn rate to the cartridge — and why the wrong powder is dangerous, since a fast powder in a rifle case can spike pressure catastrophically. Charges weighed for consistent speed.</p></div>
          <div class="step"><div class="no">05</div><h3>Seat</h3><p>Seating depth and jump to the lands drive both speed and consistency. Too long jams the lands and raises pressure; too short adds jump — we find and tune the sweet spot.</p></div>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ FINAL CTA ============ --}}
  <x-site.cta-band />

</x-layouts.site>
