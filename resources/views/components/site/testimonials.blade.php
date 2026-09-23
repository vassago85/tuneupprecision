@props(['testimonials'])

@php
    /** @var \Illuminate\Support\Collection $items */
    $items = collect($testimonials)->values();
@endphp

@if ($items->isNotEmpty())
  <section
    id="testimonials"
    x-data="{
      atStart: true,
      atEnd: false,
      timer: null,
      sync() {
        const el = this.$refs.scroller;
        if (!el) return;
        this.atStart = el.scrollLeft <= 4;
        this.atEnd = el.scrollLeft + el.clientWidth >= el.scrollWidth - 8;
      },
      step(dir) {
        const el = this.$refs.scroller;
        if (!el) return;
        const card = el.querySelector('.tst-card');
        const w = card ? card.getBoundingClientRect().width + 16 : 360;
        if (dir > 0 && this.atEnd) el.scrollTo({ left: 0, behavior: 'smooth' });
        else if (dir < 0 && this.atStart) el.scrollTo({ left: el.scrollWidth, behavior: 'smooth' });
        else el.scrollBy({ left: dir * w, behavior: 'smooth' });
      },
      start() {
        if ({{ $items->count() }} < 2) return;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        this.stop();
        this.timer = setInterval(() => this.step(1), 6000);
      },
      stop() { if (this.timer) { clearInterval(this.timer); this.timer = null; } }
    }"
    x-init="$nextTick(() => { sync(); start(); })"
    @mouseenter="stop()"
    @mouseleave="start()"
    @keydown.arrow-right="step(1)"
    @keydown.arrow-left="step(-1)"
  >
    <div class="wrap">
      <div class="tst-head">
        <div class="sec-head reveal">
          <span class="eyebrow">In their words</span>
          <h2>What shooters say.</h2>
        </div>
        @if ($items->count() > 1)
          <div class="tst-nav">
            <button type="button" class="tst-arrow" @click="step(-1)" aria-label="Previous testimonial">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>
            </button>
            <button type="button" class="tst-arrow" @click="step(1)" aria-label="Next testimonial">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
            </button>
          </div>
        @endif
      </div>

      <div class="tst-scroll reveal" x-ref="scroller" @scroll.passive="sync()">
        @foreach ($items as $t)
          <article class="tst-card">
            <div class="tst-eyebrow">{{ $t->displayEventLabel() }}</div>
            <blockquote class="tst-body">
              <span class="tst-open">&ldquo;</span>{{ $t->body }}<span class="tst-close">&rdquo;</span>
            </blockquote>
            <div class="tst-author">{{ $t->author_name }}</div>
          </article>
        @endforeach
      </div>
    </div>
  </section>
@endif
