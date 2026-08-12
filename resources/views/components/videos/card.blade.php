@props([
    'video',
])

@php
    $user = auth()->user();
    $isVerified = $user?->isVerifiedMember() ?? false;
    $isGated = (bool) $video->is_members_only;

    // Three viewer states:
    //   'play'   - allowed to watch (public video, or gated + verified)
    //   'guest'  - gated + not signed in                    -> prompt sign in
    //   'wait'   - gated + signed in but not verified yet   -> "awaiting access"
    $state = match (true) {
        ! $isGated => 'play',
        $isGated && ! $user => 'guest',
        $isGated && $user && ! $isVerified => 'wait',
        default => 'play',
    };

    $thumb = $video->thumbnailUrl('thumb');
    $playUrl = $video->videoUrl();
    $isNative = $video->hasNativeVideo();
@endphp

<article class="video-card">
  @switch ($state)
    @case ('play')
      <button type="button"
              class="video-facade"
              data-embed="{{ $playUrl }}"
              data-native="{{ $isNative ? '1' : '0' }}"
              aria-label="Play {{ $video->title }}">
        @if ($thumb)
          <img src="{{ $thumb }}" alt="{{ $video->title }}" loading="lazy" width="400" height="225">
        @else
          <span class="video-thumb-blank" aria-hidden="true"></span>
        @endif
        <span class="video-play" aria-hidden="true">
          <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z" fill="currentColor"/></svg>
        </span>
        @if ($isGated)
          <span class="video-tag">Members</span>
        @endif
      </button>
      @break

    @case ('guest')
      <a class="video-facade locked"
         href="{{ route('login') }}"
         aria-label="Sign in to watch {{ $video->title }}">
        @if ($thumb)
          <img src="{{ $thumb }}" alt="{{ $video->title }}" loading="lazy" width="400" height="225">
        @else
          <span class="video-thumb-blank" aria-hidden="true"></span>
        @endif
        <span class="video-lock">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <rect x="5" y="11" width="14" height="9" rx="2" fill="none" stroke="currentColor" stroke-width="2"/>
            <path d="M8 11V8a4 4 0 0 1 8 0v3" fill="none" stroke="currentColor" stroke-width="2"/>
          </svg>
          <span>Sign in to watch</span>
        </span>
      </a>
      @break

    @case ('wait')
      <div class="video-facade locked pending" role="img" aria-label="Awaiting access to {{ $video->title }}">
        @if ($thumb)
          <img src="{{ $thumb }}" alt="{{ $video->title }}" loading="lazy" width="400" height="225">
        @else
          <span class="video-thumb-blank" aria-hidden="true"></span>
        @endif
        <span class="video-lock">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/>
            <path d="M12 7v6l4 2" fill="none" stroke="currentColor" stroke-width="2"/>
          </svg>
          <span>Awaiting access</span>
        </span>
      </div>
      @break
  @endswitch

  <div class="video-meta">
    <h3 class="video-title">{{ $video->title }}</h3>
    @if ($video->caption)
      <p class="video-caption">{{ $video->caption }}</p>
    @endif
    @if ($state === 'wait')
      <p class="video-caption soft">Dirk will verify your account soon.</p>
    @endif
  </div>
</article>
