@props([
    'eyebrow' => 'The kit',
    'title' => 'Wear the process.',
    'copy' => "Range-tested apparel and essentials — merch that earns its place on the line.",
    'ctaLabel' => 'Browse the shop',
    // Defaults to Tune Up's own shop. Pass a full http(s) URL as the ctaHref
    // prop to point at a third-party partner instead. External URLs are
    // auto-detected and get target="_blank", rel="noopener noreferrer" and
    // an external-link icon.
    'ctaHref' => null,
])

@php
    // The loop-band auto-lights-up when real assets appear on disk. Until then
    // it renders a CSS-only "coming soon" tile with the reticle mark centred.
    //
    // Drop the files in these paths (no code changes needed):
    //   public/videos/hero-loop.mp4        - H.264 Main, yuv420p, faststart, no audio
    //   public/videos/hero-loop.webm       - optional; not used for playback (iOS
    //                                        will not autoplay a <source> child)
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
    // iOS Safari autoplays a muted inline video when the URL is the video's
    // own src. A <source> child (and WebM, which iOS cannot play) is ignored
    // for that check, so the phone stays on the poster. MP4 wins when both
    // files exist.
    $playUrl = $mp4Url ?? $webmUrl;
    $href = $ctaHref ?? route('shop');

    // Treat any http(s) href that isn't ours as an external link so we can
    // safely open in a new tab and add rel="noopener".
    $isExternal = is_string($href)
        && preg_match('~^https?://~i', $href) === 1
        && ! str_starts_with($href, url('/'));

    // Precompute conditional attributes here so we never put Blade directives
    // inside an HTML tag opener (Blade's compiler mangles @if/@endif in that
    // position and PHP throws a parse error).
    $ctaAttrs = $isExternal ? ' target="_blank" rel="noopener noreferrer"' : '';
    $videoPosterAttr = $hasPoster ? ' poster="'.e($posterUrl).'"' : '';
@endphp

<section class="loop-band">
  <div class="wrap loop-grid">
    <div class="loop-copy reveal">
      <span class="eyebrow">{{ $eyebrow }}</span>
      <h2>{{ $title }}</h2>
      <p>{{ $copy }}</p>
      <a href="{{ $href }}" class="btn btn-primary"{!! $ctaAttrs !!}>{{ $ctaLabel }}
        @if ($isExternal)
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 4h6v6M20 4l-9 9M10 5H5v14h14v-5"/></svg>
        @else
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        @endif
      </a>
    </div>

    {{-- No .reveal here. That class is opacity:0 until scroll, and both
         iOS Safari and Chrome Android refuse to start a muted inline
         video that is not actually visible. They also do not resume it
         once the fade finishes. --}}
    <div class="loop-frame" data-loop>
      @if ($hasVideo)
        {{-- muted before autoplay, and src on the video itself (not a
             <source> child). iOS decides autoplay from those attributes
             before any script runs, and it ignores autoplay on <source>. --}}
        <video class="loop-video"
               src="{{ $playUrl }}"
               muted="muted"
               playsinline
               webkit-playsinline
               autoplay
               loop
               preload="auto"{!! $videoPosterAttr !!}
               disablepictureinpicture
               disableremoteplayback
               aria-hidden="true"></video>
        @if ($hasPoster)
          {{-- Hidden while the video can play. Shown only for reduced-motion,
               where the video itself is hidden. Kept out of the paint tree
               otherwise so it cannot cover the video and block mobile autoplay. --}}
          <img class="loop-poster loop-poster-still"
               src="{{ $posterUrl }}"
               alt=""
               width="1280" height="720"
               loading="lazy" decoding="async">
        @endif
      @elseif ($hasPoster)
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
        <button type="button" class="loop-play-cue" aria-label="Play video">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M8 5v14l11-7z"/></svg>
        </button>
        <button type="button" class="loop-pause" aria-label="Pause video" aria-pressed="false">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M6 5h4v14H6zM14 5h4v14h-4z"/></svg>
        </button>
      @endif
    </div>
  </div>
</section>
