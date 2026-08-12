@props([
    'eyebrow' => 'The kit',
    'title' => 'Wear the process.',
    'copy' => "Range-tested apparel and essentials — merch that earns its place on the line.",
    'ctaLabel' => 'Browse the shop',
    // Defaults to Tune Up's own shop. To point the band at a third-party
    // partner instead (e.g. an outdoor brand), pass a full URL from the view:
    //   <x-site.loop-band ctaHref="https://partner.example.com" ctaLabel="Shop Partner" />
    // The component auto-detects external URLs and adds target="_blank" +
    // rel="noopener noreferrer" plus an external-link icon.
    'ctaHref' => null,
])

@php
    // The loop-band auto-lights-up when real assets appear on disk. Until then
    // it renders a CSS-only "coming soon" tile with the reticle mark centred.
    //
    // Drop the files in these paths (no code changes needed):
    //   public/videos/hero-loop.mp4        - H.264 fallback, universal
    //   public/videos/hero-loop.webm       - VP9/AV1 primary, smaller (optional)
    //   public/images/hero-loop-poster.webp - poster image ~1280x720 (optional)
    //
    // The rest is just cache-busting so re-encodes don't get stuck in browsers.
    $mp4Path = public_path('videos/hero-loop.mp4');
    $webmPath = public_path('videos/hero-loop.webm');
    $posterPath = public_path('images/hero-loop-poster.webp');

    $hasMp4 = is_file($mp4Path);
    $hasWebm = is_file($webmPath);
    $hasPoster = is_file($posterPath);

    $mp4Url = $hasMp4 ? asset('videos/hero-loop.mp4').'?v='.filemtime($mp4Path) : null;
    $webmUrl = $hasWebm ? asset('videos/hero-loop.webm').'?v='.filemtime($webmPath) : null;
    $posterUrl = $hasPoster ? asset('images/hero-loop-poster.webp').'?v='.filemtime($posterPath) : null;

    $hasVideo = $hasMp4 || $hasWebm;
    $href = $ctaHref ?? route('shop');

    // Treat any http(s) href that isn't ours as an external link so we can
    // safely open in a new tab and add rel="noopener".
    $isExternal = is_string($href)
        && preg_match('~^https?://~i', $href) === 1
        && ! str_starts_with($href, url('/'));
@endphp

<section class="loop-band">
  <div class="wrap loop-grid">
    <div class="loop-copy reveal">
      <span class="eyebrow">{{ $eyebrow }}</span>
      <h2>{{ $title }}</h2>
      <p>{{ $copy }}</p>
      <a href="{{ $href }}" class="btn btn-primary"
         @if ($isExternal) target="_blank" rel="noopener noreferrer" @endif>{{ $ctaLabel }}
        @if ($isExternal)
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 4h6v6M20 4l-9 9M10 5H5v14h14v-5"/></svg>
        @else
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        @endif
      </a>
    </div>

    <div class="loop-frame reveal" data-loop>
      @if ($hasPoster)
        <img class="loop-poster"
             src="{{ $posterUrl }}"
             alt="{{ $title }}"
             width="1280" height="720"
             loading="lazy" decoding="async">
      @else
        {{-- CSS-only fallback until a real poster/video lands on disk. --}}
        <div class="loop-poster loop-placeholder" role="img" aria-label="Coming soon — Tune Up kit">
          <svg class="mark" viewBox="0 0 40 40" aria-hidden="true">
            <circle cx="20" cy="20" r="18" fill="none" stroke="currentColor" stroke-width="1.4"/>
            <line x1="20" y1="2"  x2="20" y2="14" stroke="currentColor" stroke-width="1.4"/>
            <line x1="20" y1="26" x2="20" y2="38" stroke="currentColor" stroke-width="1.4"/>
            <line x1="2"  y1="20" x2="14" y2="20" stroke="currentColor" stroke-width="1.4"/>
            <line x1="26" y1="20" x2="38" y2="20" stroke="currentColor" stroke-width="1.4"/>
            <circle cx="20" cy="20" r="3.4" fill="#D45B2E"/>
          </svg>
          <span class="loop-placeholder-tag">Coming soon</span>
        </div>
      @endif

      @if ($hasVideo)
        <video class="loop-video"
               muted loop playsinline
               preload="metadata"
               @if ($hasPoster) poster="{{ $posterUrl }}" @endif
               aria-hidden="true">
          @if ($hasWebm)
            <source src="{{ $webmUrl }}" type="video/webm">
          @endif
          @if ($hasMp4)
            <source src="{{ $mp4Url }}" type="video/mp4">
          @endif
        </video>
      @endif
    </div>
  </div>
</section>
