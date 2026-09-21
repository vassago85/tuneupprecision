@props(['testimonials'])

@php
    /** @var \Illuminate\Support\Collection $items */
    $items = collect($testimonials)->values();
@endphp

@if ($items->isNotEmpty())
  <section
    id="testimonials"
    x-data="{
      i: 0,
      count: {{ $items->count() }},
      timer: null,
      next() { this.i = (this.i + 1) % this.count; },
      prev() { this.i = (this.i - 1 + this.count) % this.count; },
      start() { if (this.count > 1) { this.stop(); this.timer = setInterval(() => this.next(), 6000); } },
      stop()  { if (this.timer) { clearInterval(this.timer); this.timer = null; } }
    }"
    x-init="start()"
    @mouseenter="stop()"
    @mouseleave="start()"
    @keydown.arrow-right.window="next()"
    @keydown.arrow-left.window="prev()"
  >
    <div class="wrap">
      <div class="sec-head reveal">
        <span class="eyebrow">In their words</span>
        <h2>What shooters say.</h2>
      </div>

      <div class="tst-frame reveal">
        @if ($items->count() > 1)
          <button type="button" class="tst-arrow prev" @click="prev()" aria-label="Previous testimonial">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>
          </button>
          <button type="button" class="tst-arrow next" @click="next()" aria-label="Next testimonial">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
          </button>
        @endif

        <div class="tst-track">
          @foreach ($items as $index => $t)
            <article class="tst-card" x-show="i === {{ $index }}" x-cloak x-transition.opacity.duration.400ms>
              <div class="tst-eyebrow">{{ $t->displayEventLabel() }}</div>
              <blockquote class="tst-body">
                <span class="tst-open">&ldquo;</span>{{ $t->body }}<span class="tst-close">&rdquo;</span>
              </blockquote>
              <div class="tst-author">{{ $t->author_name }}</div>
            </article>
          @endforeach
        </div>

        @if ($items->count() > 1)
          <div class="tst-dots" role="tablist" aria-label="Testimonial navigation">
            @foreach ($items as $index => $t)
              <button type="button" class="tst-dot" role="tab"
                      :class="{ active: i === {{ $index }} }"
                      :aria-selected="i === {{ $index }}"
                      @click="i = {{ $index }}"
                      aria-label="Show testimonial {{ $index + 1 }}"></button>
            @endforeach
          </div>
        @endif
      </div>
    </div>
  </section>
@endif
