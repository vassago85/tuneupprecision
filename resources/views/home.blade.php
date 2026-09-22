<x-layouts.site
    description="Long range rifle training with Dirk Pio in South Africa. Reloading, PRS Shooting, Precision Long Range and Handgun Fundamentals — small squads at a private facility, your own rifle."
>

  {{-- =====================================================================
       Homepage rhythm — three background bands, one shared content grid.
       .wrap (max-width:1180px, 26px inline pad) is the ONLY horizontal
       container on this page; every section aligns to it. Backgrounds are
       carried by .band-tint / .band-dark wrappers so the eye reads three
       deliberate regions rather than seven independent components:

         LIGHT  →  Hero, Why Tune Up (thesis)
         TINT   →  Fundamentals pillars, Testimonials, Meet Dirk
         DARK   →  What you'll learn, Wear the process
         LIGHT  →  Newsletter + footer (from the layout wrapper)

       No reticle dividers between sections — the .eyebrow motif + the band
       transitions carry the rhythm.
       ===================================================================== --}}

  {{-- ============ HERO — LIGHT ============ --}}
  <span id="top"></span>
  <section class="hero">
    <div class="wrap hero-grid">
      <div class="hero-copy reveal">
        <span class="eyebrow">Aiming for consistent long range impacts.</span>
        <h1>Dial in<br>your <span class="cop">distance.</span></h1>
        <p class="lead">From precision handloading to consistent impacts at extended distances, Tune Up covers every step of the process. Our small group, one-on-one, and squad training days are all structured around a single objective. Repeatable, consistent performance.</p>
        <div class="hero-cta">
          <a href="{{ route('courses') }}" class="btn btn-primary">Book a course
            <svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </a>
          <a href="{{ route('shop') }}" class="btn btn-ghost">Browse the shop</a>
        </div>
        <div class="hero-data">
          <div class="cell"><div class="k">Precision hand loading</div><div class="v">Bench to barrel.</div></div>
          <div class="cell"><div class="k">Long range precision</div><div class="v">Far steel, called.</div></div>
          <div class="cell"><div class="k">PRS "positional"</div><div class="v">On the clock.</div></div>
          <div class="cell"><div class="k">Handgun fundamentals</div><div class="v">Safe. Consistent. Confident.</div></div>
        </div>
      </div>
      <div class="hero-badge reveal">
        <img class="brand-emblem" src="{{ asset('images/logo.png') }}?v={{ @filemtime(public_path('images/logo.png')) ?: '2' }}" alt="Tune Up — Long Range Precision Training" width="658" height="557">
      </div>
    </div>
  </section>

  {{-- ============ WHY TUNE UP (thesis) — LIGHT ============ --}}
  {{-- Flows straight out of the hero on the same cream background so the --}}
  {{-- introduction reads as one continuous idea, not a new page. --}}
  <section id="training" class="section-flush-top">
    <div class="wrap">
      <div class="sec-head reveal">
        <span class="eyebrow">Why train here</span>
        <h2>Fundamentals and data driven.</h2>
        <p>No magic, no gimmicks. Just the disciplined process good shooters and reloaders actually run, taught step by step.</p>
      </div>
      <div class="values values-thesis">
        <div class="val val-thesis reveal">
          <div class="ic"><svg viewBox="0 0 24 24"><path d="M4 20h16"/><path d="M6 20V10l6-4 6 4v10"/><path d="M10 20v-5h4v5"/></svg></div>
          <h3>Fundamentals first</h3>
          <p>The shortcut to skill isn't more range time. It's the fundamentals. You don't know what you don't know.</p>
        </div>
        <div class="val val-thesis reveal">
          <div class="ic"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 6v12"/><path d="M15 9h-3.5a2 2 0 0 0 0 4h3a2 2 0 0 1 0 4H9"/></svg></div>
          <h3>Coaching saves money</h3>
          <p>No amount of solo range time gets you past your plateau. A coached day pays for itself in components and hours.</p>
        </div>
        <div class="val val-thesis reveal">
          <div class="ic"><svg viewBox="0 0 24 24"><path d="M8 21h8"/><path d="M12 17v4"/><path d="M6 4h12v4a6 6 0 0 1-12 0z"/><path d="M6 5H4a2 2 0 0 0 0 4h2"/><path d="M18 5h2a2 2 0 0 1 0 4h-2"/></svg></div>
          <h3>Confidence and competence</h3>
          <p>A better shooter walks off the line, with results you wouldn't reach alone.</p>
        </div>
      </div>
    </div>
  </section>

  {{-- ============ TINT BAND ============ ============================== --}}
  {{-- Testimonials → Meet Dirk. Shared off-white bg pulls "proof → trust" --}}
  {{-- into one region between the thesis (light) and the dark learning     --}}
  {{-- block below.                                                         --}}
  <div class="band band-tint">

    {{-- Testimonials — flat social-proof strip, not a page destination. --}}
    <x-site.testimonials :testimonials="$testimonials ?? collect()" />

    {{-- Meet Dirk — the human anchor before the site starts selling training. --}}
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
          <p>Tune Up runs on one idea: long range precision, shooting and reloading alike, is a process you can learn, not a talent you're born with. Dirk coaches from the same process he runs on the line and at the bench: methodically, with the data to back every call.</p>
          <p>You'll leave with a rifle and a load you trust, a DOPE you built yourself, and the confidence to make the shot when it counts.</p>
          <div class="creds">
            <div class="cred"><div class="n">10+</div><div class="l">Years on the line</div></div>
          </div>
        </div>
      </div>
    </section>

  </div>

  {{-- ============ DARK BAND =========================================== --}}
  {{-- What you'll learn → Wear the process. Same navy across both so it --}}
  {{-- reads as one dark region, separated only by internal whitespace.  --}}
  @php
      // Only show tabs for disciplines that actually have a learnings list.
      $learnDisciplines = ($disciplineTypes ?? collect())
          ->filter(fn ($type) => ! empty($type->learnings))
          ->values();
      $defaultTab = $learnDisciplines->first()?->slug;
  @endphp
  <div class="band band-dark">

    @if ($learnDisciplines->isNotEmpty())
      <section id="process">
        <div class="wrap" x-data="{ tab: @js($defaultTab) }">
          <div class="sec-head reveal">
            <span class="eyebrow">Every discipline</span>
            <h2>What you'll learn.</h2>
            <p>Pick a discipline to see the skills, safety and technique you'll walk away with.</p>
          </div>

          <div class="proc-tabbar reveal" role="tablist">
            @foreach ($learnDisciplines as $type)
              <button type="button" role="tab"
                      :class="{ active: tab === {{ Illuminate\Support\Js::from($type->slug) }} }"
                      @click="tab = {{ Illuminate\Support\Js::from($type->slug) }}">{{ $type->name }}</button>
            @endforeach
          </div>

          @foreach ($learnDisciplines as $type)
            <div x-show="tab === {{ Illuminate\Support\Js::from($type->slug) }}" x-cloak>
              @if ($type->blurb)
                <p class="proc-meta">{{ $type->blurb }}</p>
              @endif
              <div class="steps learn-steps">
                @foreach ($type->learnings as $index => $bullet)
                  <div class="step">
                    <div class="no">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</div>
                    <h3>{{ $bullet }}</h3>
                  </div>
                @endforeach
              </div>
            </div>
          @endforeach
        </div>
      </section>
    @endif

    {{-- Wear the process — same navy, just the natural close of the dark region. --}}
    <x-site.loop-band />

  </div>

</x-layouts.site>
