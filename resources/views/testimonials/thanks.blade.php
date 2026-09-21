@php
    $shareMessage = "I just did a training day with Tune Up Precision — check them out: ".url('/');
    $waHref = 'https://wa.me/?text='.rawurlencode($shareMessage);
@endphp
<x-layouts.site
    title="Thanks for the testimonial"
    description="Thanks for taking the time — your testimonial is queued for a quick read before it appears on the site."
>
  <section class="auth-wrap">
    <div class="wrap">
      <div class="auth-card reveal" style="text-align:center">
        <div class="sec-head" style="margin-bottom:22px">
          <span class="eyebrow">Testimonial in</span>
          <h2 style="font-size:36px">Thanks — got it.</h2>
          <p>Dirk will give it a quick read and publish it to the site. If anything needs a small edit for length or clarity, we'll flick you an email first.</p>
        </div>

        <div style="display:flex;flex-direction:column;gap:12px;align-items:stretch">
          <a href="{{ $waHref }}" target="_blank" rel="noopener" class="btn btn-primary" style="justify-content:center">
            Share on WhatsApp
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </a>
          <a href="{{ route('home') }}" class="btn btn-ghost" style="justify-content:center">Back to the site</a>
        </div>
      </div>
    </div>
  </section>
</x-layouts.site>
