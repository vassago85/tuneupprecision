{{-- Footer, extracted from the approved mockup. --}}
<footer>
  <div class="wrap">
    <div class="nl" id="newsletter">
      <div class="nl-copy">
        <h3>Monthly newsletter</h3>
        <p>Upcoming course dates, competition invites and range notes — once a month. No spam, unsubscribe any time.</p>
      </div>
      <form class="nl-form" method="POST" action="{{ route('newsletter.subscribe') }}">
        @csrf
        {{-- Honeypot: hidden from people, bots tend to fill it. --}}
        <div class="nl-hp" aria-hidden="true">
          <label>Company (leave blank)
            <input type="text" name="company" tabindex="-1" autocomplete="off">
          </label>
        </div>
        <input type="hidden" name="ts" value="{{ time() }}">
        <input type="email" name="email" required placeholder="you@email.com" aria-label="Email address" value="{{ old('email') }}">
        <button type="submit" class="btn btn-primary">Subscribe</button>
      </form>
      @if (session('newsletter_status') === 'success')
        <p class="nl-msg nl-ok">Thanks — you're on the list. Look out for the monthly note.</p>
      @elseif ($errors->has('email'))
        <p class="nl-msg nl-err">{{ $errors->first('email') }}</p>
      @endif
    </div>
    <div class="foot-grid">
      <div class="foot-brand">
        <a class="brand" href="{{ url('/') }}" aria-label="Tune Up home">
          <svg class="mark" viewBox="0 0 40 40" aria-hidden="true">
            <circle cx="20" cy="20" r="18" fill="none" stroke="#2C3E50" stroke-width="2"/>
            <line x1="20" y1="2" x2="20" y2="14" stroke="#2C3E50" stroke-width="2"/><line x1="20" y1="26" x2="20" y2="38" stroke="#2C3E50" stroke-width="2"/>
            <line x1="2" y1="20" x2="14" y2="20" stroke="#2C3E50" stroke-width="2"/><line x1="26" y1="20" x2="38" y2="20" stroke="#2C3E50" stroke-width="2"/>
            <circle cx="20" cy="20" r="3.4" fill="#D45B2E"/>
          </svg>
          <span class="wm"><b>TUNE UP</b><span>LONG RANGE PRECISION</span></span>
        </a>
        <p>Small-squad long range precision instruction — shooting and reloading — and gear. Gauteng, South Africa.</p>
        <div class="socials">
          <a href="#" aria-label="Instagram"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg></a>
          <a href="#" aria-label="Facebook"><svg viewBox="0 0 24 24"><path d="M15 3h-3a4 4 0 0 0-4 4v3H5v4h3v7h4v-7h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
          <a href="#" aria-label="WhatsApp"><svg viewBox="0 0 24 24"><path d="M3 21l1.6-4.2A8 8 0 1 1 12 20a8 8 0 0 1-4-1L3 21z"/><path d="M8.5 9c0 3 2.5 5.5 5.5 5.5M8.5 9c0-.6.4-1 1-1M14 14.5c.6 0 1-.4 1-1"/></svg></a>
        </div>
      </div>
      <div class="foot-col">
        <h3>Training</h3>
        @foreach (\App\Models\TrainingType::query()->activeOrdered()->get() as $type)
          <a href="{{ route('courses', ['type' => $type->slug]) }}">{{ $type->name }}</a>
        @endforeach
        <a href="{{ route('courses') }}">One-on-one</a>
      </div>
      <div class="foot-col">
        <h3>Explore</h3>
        <a href="{{ url('/#about') }}">About Dirk</a>
        <a href="{{ url('/#process') }}">How a day runs</a>
        <a href="{{ route('shop') }}">The shop</a>
        <a href="{{ route('calendar') }}">Course calendar</a>
      </div>
      <div class="foot-col">
        <h3>Contact</h3>
        <a href="mailto:hello@tuneupprecision.co.za">hello@tuneupprecision.co.za</a>
        {{-- WhatsApp: add real number here as a wa.me link once available. --}}
        <p>Gauteng · by arrangement</p>
      </div>
    </div>
    <div class="foot-bottom">
      <span>© {{ date('Y') }} Tune Up Long Range Precision Training</span>
      <span>Shoot safe · Know your target and beyond</span>
    </div>
  </div>
</footer>
