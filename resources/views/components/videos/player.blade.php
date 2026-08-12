@props([
    'video',
])

@php
    $user = auth()->user();
    $isVerified = $user?->isVerifiedMember() ?? false;
    $isGated = (bool) $video->is_members_only;

    $state = match (true) {
        ! $isGated => 'play',
        $isGated && ! $user => 'guest',
        $isGated && $user && ! $isVerified => 'wait',
        default => 'play',
    };

    $poster = $video->thumbnailUrl('wide');
    $playUrl = $video->videoUrl();
    $isNative = $video->hasNativeVideo();
@endphp

<div class="video-featured">
  <div class="video-featured-media">
    @switch ($state)
      @case ('play')
        <button type="button"
                class="video-facade video-facade-lg"
                data-embed="{{ $playUrl }}"
                data-native="{{ $isNative ? '1' : '0' }}"
                aria-label="Play {{ $video->title }}">
          @if ($poster)
            <img src="{{ $poster }}" alt="{{ $video->title }}" width="1280" height="720">
          @else
            <span class="video-thumb-blank" aria-hidden="true"></span>
          @endif
          <span class="video-play video-play-lg" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z" fill="currentColor"/></svg>
          </span>
        </button>
        @break

      @case ('guest')
        <a class="video-facade video-facade-lg locked"
           href="{{ route('login') }}"
           aria-label="Sign in to watch {{ $video->title }}">
          @if ($poster)
            <img src="{{ $poster }}" alt="{{ $video->title }}" width="1280" height="720">
          @else
            <span class="video-thumb-blank" aria-hidden="true"></span>
          @endif
          <span class="video-lock video-lock-lg">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <rect x="5" y="11" width="14" height="9" rx="2" fill="none" stroke="currentColor" stroke-width="2"/>
              <path d="M8 11V8a4 4 0 0 1 8 0v3" fill="none" stroke="currentColor" stroke-width="2"/>
            </svg>
            <span>Sign in to watch</span>
          </span>
        </a>
        @break

      @case ('wait')
        <div class="video-facade video-facade-lg locked pending">
          @if ($poster)
            <img src="{{ $poster }}" alt="{{ $video->title }}" width="1280" height="720">
          @else
            <span class="video-thumb-blank" aria-hidden="true"></span>
          @endif
          <span class="video-lock video-lock-lg">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/>
              <path d="M12 7v6l4 2" fill="none" stroke="currentColor" stroke-width="2"/>
            </svg>
            <span>Awaiting access</span>
          </span>
        </div>
        @break
    @endswitch
  </div>

  <div class="video-featured-meta">
    <span class="eyebrow">Featured</span>
    <h3>{{ $video->title }}</h3>
    @if ($video->caption)
      <p>{{ $video->caption }}</p>
    @endif
  </div>
</div>
