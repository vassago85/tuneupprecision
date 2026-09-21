@php
    $courseSchemas = $disciplines->map(function ($d) {
        $type = $d['type'];
        $tpl = $d['representative'];
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Course',
            'name' => $tpl?->title ?? $type->name,
            'description' => $tpl?->blurb ?? $type->blurb,
            'provider' => [
                '@type' => 'Organization',
                'name' => 'Tune Up Precision',
                'sameAs' => url('/'),
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => $d['from_price_cents'] > 0 ? number_format(((int) $d['from_price_cents']) / 100, 2, '.', '') : null,
                'priceCurrency' => 'ZAR',
                'availability' => $d['events']->contains(fn ($e) => ! $e->isFull())
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/SoldOut',
                'url' => route('courses'),
            ],
            'hasCourseInstance' => $d['events']->map(fn ($e) => [
                '@type' => 'CourseInstance',
                'courseMode' => 'onsite',
                'startDate' => $e->starts_on?->toDateString(),
                'endDate' => ($e->ends_on ?? $e->starts_on)?->toDateString(),
                'location' => $e->venue ? [
                    '@type' => 'Place',
                    'name' => $e->venue,
                    'address' => ['@type' => 'PostalAddress', 'addressCountry' => 'ZA'],
                ] : null,
            ])->values()->all(),
        ];
    })->values()->all();

    $coursesJsonLd = ['@context' => 'https://schema.org', '@graph' => $courseSchemas];
@endphp
<x-layouts.site
    title="Courses"
    description="Book a full-day precision course — Reloading, PRS Shooting, Precision Long Range or Handgun Fundamentals — with Dirk Pio at Tune Up Precision. Upcoming dates and prices per discipline."
    :json-ld="$coursesJsonLd"
>

  {{-- ============ COURSE AGENDA ============ --}}
  <section>
    <div class="wrap">
      <div class="sec-head reveal">
        <span class="eyebrow">What you can book</span>
        <h2>Four disciplines. Upcoming dates below each.</h2>
        <p>Full days at a private facility — on the line, at the bench or on the pistol range, depending on the discipline. Bring your firearm and ammo; targets and use of the ballistic and reloading kit are included.</p>
      </div>

      <div class="courses">
        @foreach ($disciplines as $discipline)
          <x-training.discipline-card
            :type="$discipline['type']"
            :representative="$discipline['representative']"
            :events="$discipline['events']"
            :from-price-cents="$discipline['from_price_cents']"
            :price-is-from="$discipline['price_is_from']"
            :featured="$discipline['type']->slug === 'long-range-prone'"
          />
        @endforeach
      </div>

      {{-- One-on-one coaching --}}
      <div class="private reveal">
        <div class="txt">
          <h3>One-on-one coaching</h3>
          <p>A full day built entirely around you and your rifle — load development, a problem you can't crack, or match prep for a specific stage. Quoted individually depending on what's required.</p>
        </div>
        <div class="p2">
          <div class="amt">On request <s>Quoted per day · scoped to what you need</s></div>
          <a href="{{ route('contact.create', ['subject' => 'One-on-one coaching']) }}" class="btn btn-primary book" data-course="One-on-one coaching">Enquire</a>
        </div>
      </div>
    </div>
  </section>

  <x-site.cta-band />

</x-layouts.site>
