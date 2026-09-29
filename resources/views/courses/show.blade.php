@php
    $courseSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Course',
        'name' => $course->title,
        'description' => $course->blurb ?: $type->blurb,
        'provider' => [
            '@type' => 'Organization',
            'name' => 'Tune Up Precision',
            'sameAs' => url('/'),
        ],
        'image' => $course->shareImageUrl(),
        'offers' => [
            '@type' => 'Offer',
            'price' => $fromPriceCents > 0 ? number_format(((int) $fromPriceCents) / 100, 2, '.', '') : null,
            'priceCurrency' => 'ZAR',
            'availability' => $events->contains(fn ($e) => ! $e->isFull())
                ? 'https://schema.org/InStock'
                : 'https://schema.org/SoldOut',
            'url' => route('courses.show', $course),
        ],
        'hasCourseInstance' => $events->map(fn ($e) => [
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
@endphp
<x-layouts.site
    :title="$course->title"
    :description="$course->blurb ?: ($type->blurb ?: $course->title.' with Dirk Pio at Tune Up Precision.')"
    :image="$course->shareImageUrl()"
    :canonical="route('courses.show', $course)"
    :json-ld="$courseSchema"
>
  <section>
    <div class="wrap">
      <p class="shop-crumb reveal">
        <a href="{{ route('courses') }}">Courses</a>
        <span>/</span>
        {{ $type->name }}
      </p>

      <div class="courses courses-single">
        <x-training.discipline-card
          :type="$type"
          :representative="$course"
          :templates="$templates"
          :events="$events"
          :from-price-cents="$fromPriceCents"
          :price-is-from="$priceIsFrom"
          :featured="$type->slug === 'long-range-prone'"
          :linked="false"
        />
      </div>
    </div>
  </section>
</x-layouts.site>
