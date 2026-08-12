<x-layouts.site title="The Range">
  {{-- ============ HERO ============ --}}
  <section class="range-hero">
    <div class="wrap">
      <div class="sec-head reveal">
        <span class="eyebrow">Watch the process</span>
        <h2>Roll the footage.</h2>
        <p>Short videos from the line and the bench — drills, ballistic-data walk-throughs, and reloading steps you can rewatch between range days.</p>
      </div>

      @auth
        @if (! auth()->user()->isVerifiedMember())
          <div class="auth-note info reveal">
            You're signed in, but Dirk hasn't verified your account yet. Members-only videos will unlock as soon as he does.
          </div>
        @endif
      @endauth
    </div>
  </section>

  {{-- ============ FEATURED ============ --}}
  @if ($featured)
    <section class="range-featured">
      <div class="wrap">
        <div class="reveal">
          <x-videos.player :video="$featured" />
        </div>
      </div>
    </section>
  @endif

  {{-- ============ LIBRARY (tabs by discipline) ============ --}}
  <section class="range-lib">
    @php
      // Prefer types that actually have videos; fall back to the first
      // active discipline so the tab bar is never empty.
      $typesWithVideos = $trainingTypes->filter(
          fn ($t) => ($videosByType[$t->slug] ?? collect())->isNotEmpty()
      )->values();

      $initialTab = optional($typesWithVideos->first())->slug
          ?? optional($trainingTypes->first())->slug
          ?? 'all';
    @endphp

    <div class="wrap" x-data="{ tab: '{{ $initialTab }}' }">
      <div class="sec-head reveal">
        <span class="eyebrow">The library</span>
        <h2>Pick a discipline.</h2>
        <p>Grouped by discipline so you can pull up exactly what you need before the next range day.</p>
      </div>

      <div class="proc-tabbar reveal" role="tablist">
        @foreach ($trainingTypes as $type)
          <button type="button" role="tab"
                  :class="{ active: tab === '{{ $type->slug }}' }"
                  @click="tab = '{{ $type->slug }}'">
            {{ $type->name }}
          </button>
        @endforeach
      </div>

      @foreach ($trainingTypes as $type)
        @php $bucket = $videosByType[$type->slug] ?? collect(); @endphp
        <div x-show="tab === '{{ $type->slug }}'" x-cloak>
          @if ($type->blurb)
            <p class="proc-meta">{{ $type->blurb }}</p>
          @endif

          @if ($bucket->isEmpty())
            <p class="schedule-empty">Nothing here yet — Dirk is filming a set. Check back soon.</p>
          @else
            <div class="range-grid">
              @foreach ($bucket as $video)
                <x-videos.card :video="$video" />
              @endforeach
            </div>
          @endif
        </div>
      @endforeach
    </div>
  </section>

  <x-site.cta-band />
</x-layouts.site>
