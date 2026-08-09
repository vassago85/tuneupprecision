@props([
    'eyebrow' => 'Next intake filling up',
    'heading' => 'Book your seat on the line.',
    'text' => "Dates fill quickly. Reserve now and we'll confirm your seat and kit list by email.",
    'ctaLabel' => 'Reserve a seat',
    'ctaHref' => null,
    'note' => 'or message Dirk directly →',
])
{{-- Final CTA band, extracted from the approved mockup. --}}
<section style="padding-top:20px">
  <div class="wrap">
    <div class="cta-band reveal">
      <svg class="ret" viewBox="0 0 100 100" aria-hidden="true">
        <circle cx="50" cy="50" r="46" fill="none" stroke="#fff" stroke-width="1"/>
        <line x1="50" y1="0" x2="50" y2="100" stroke="#fff" stroke-width="1"/>
        <line x1="0" y1="50" x2="100" y2="50" stroke="#fff" stroke-width="1"/>
        <circle cx="50" cy="50" r="5" fill="#D45B2E"/>
      </svg>
      <div class="l">
        <span class="eyebrow">{{ $eyebrow }}</span>
        <h2>{{ $heading }}</h2>
        <p>{{ $text }}</p>
      </div>
      <div class="r">
        <a href="{{ $ctaHref ?? url('/#courses') }}" class="btn btn-primary">{{ $ctaLabel }}
          <svg viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
        <a class="note" href="mailto:hello@tuneupprecision.co.za?subject=Tune%20Up%20enquiry">{{ $note }}</a>
      </div>
    </div>
  </div>
</section>
