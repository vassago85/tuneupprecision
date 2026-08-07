{{-- Footer, extracted from the approved mockup. --}}
<footer>
  <div class="wrap">
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
        <p>Small-squad long range precision instruction and gear. Gauteng, South Africa.</p>
        <div class="socials">
          <a href="#" aria-label="Instagram"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg></a>
          <a href="#" aria-label="Facebook"><svg viewBox="0 0 24 24"><path d="M15 3h-3a4 4 0 0 0-4 4v3H5v4h3v7h4v-7h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a>
          <a href="#" aria-label="WhatsApp"><svg viewBox="0 0 24 24"><path d="M3 21l1.6-4.2A8 8 0 1 1 12 20a8 8 0 0 1-4-1L3 21z"/><path d="M8.5 9c0 3 2.5 5.5 5.5 5.5M8.5 9c0-.6.4-1 1-1M14 14.5c.6 0 1-.4 1-1"/></svg></a>
        </div>
      </div>
      <div class="foot-col">
        <h5>Training</h5>
        @foreach (\App\Models\TrainingType::query()->activeOrdered()->get() as $type)
          <a href="{{ url('/?type='.$type->slug) }}#courses">{{ $type->name }}</a>
        @endforeach
        <a href="{{ url('/#courses') }}">One-on-one</a>
      </div>
      <div class="foot-col">
        <h5>Explore</h5>
        <a href="{{ url('/#about') }}">About Dirk</a>
        <a href="{{ url('/#process') }}">How a day runs</a>
        <a href="{{ url('/#shop') }}">The shop</a>
        <a href="{{ url('/#courses') }}">Course calendar</a>
      </div>
      <div class="foot-col">
        <h5>Contact</h5>
        <p>hello@tuneupprecision.co.za</p>
        <p>WhatsApp: 0XX XXX XXXX</p>
        <p>Gauteng · by arrangement</p>
      </div>
    </div>
    <div class="foot-bottom">
      <span>© {{ date('Y') }} Tune Up Long Range Precision Shooting</span>
      <span>Shoot safe · Know your target and beyond</span>
    </div>
  </div>
</footer>
