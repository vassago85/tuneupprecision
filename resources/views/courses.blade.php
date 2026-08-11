<x-layouts.site title="Courses">

  {{-- ============ COURSE OFFERINGS ============ --}}
  <section>
    <div class="wrap">
      <div class="sec-head reveal">
        <span class="eyebrow">Course offerings</span>
        <h2>Pick the course. Book the date.</h2>
        <p>Every course is a full day, small squad, private facility. Bring your own rifle and ammo; targets and use of the ballistic and reloading kit are included. Check the calendar for the next date for each.</p>
      </div>

      <div class="courses">
        @forelse ($courses as $course)
          @php
            $meta = array_filter([$course->trainingType?->name, $course->level]);
            $bookHref = $course->trainingType
              ? route('calendar', ['type' => $course->trainingType->slug])
              : route('calendar');
            $featured = $course->slug === 'applied-long-range';
          @endphp
          <x-training.course-card
            :level="$meta ? implode(' · ', $meta) : null"
            :title="$course->title"
            :desc="$course->blurb"
            :specs="$course->specs ?? []"
            :price="\App\Support\Money::format((int) $course->base_price_cents, false)"
            priceNote="per shooter"
            :featured="$featured"
            :tag="$featured ? 'Signature' : null"
            :bookHref="$bookHref"
          />
        @empty
          <p class="mono" style="color:var(--muted)">New courses are being finalised — check back soon.</p>
        @endforelse
      </div>

      {{-- One-on-one coaching --}}
      <div class="private reveal">
        <div class="txt">
          <h3>One-on-one coaching</h3>
          <p>A full day built entirely around you and your rifle — load development, a problem you can't crack, or match prep for a specific stage. Quoted individually depending on what's required.</p>
        </div>
        <div class="p2">
          <div class="amt">On request <s>Quoted per day · scoped to what you need</s></div>
          <a href="mailto:hello@tuneupprecision.co.za?subject=One-on-one%20coaching" class="btn btn-primary book" data-course="One-on-one coaching">Enquire</a>
        </div>
      </div>
    </div>
  </section>

  <x-site.cta-band />

</x-layouts.site>
