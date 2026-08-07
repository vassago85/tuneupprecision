@props([
    'level' => null,
    'title' => '',
    'desc' => '',
    'specs' => [],          // associative array label => value
    'price' => null,        // display string, e.g. "R1 850"
    'priceNote' => 'per shooter',
    'featured' => false,
    'tag' => null,          // e.g. "Most booked"
    'fullyBooked' => false, // fully-booked events DO display, as "Fully booked"
    'bookHref' => null,
])
{{-- Course card, extracted from the approved mockup (.course). --}}
<div class="course {{ $featured ? 'feat' : '' }} reveal">
  @if ($tag)
    <span class="tag">{{ $tag }}</span>
  @endif
  @if ($level)
    <div class="lvl">{{ $level }}</div>
  @endif
  <h3>{{ $title }}</h3>
  <div class="desc">{{ $desc }}</div>

  <x-site.dope-card :rows="$specs" />

  <div class="price"><b>{{ $price }}</b><s>{{ $priceNote }}</s></div>

  @if ($fullyBooked)
    <button class="btn fully" type="button" disabled aria-disabled="true">Fully booked</button>
  @else
    <a href="{{ $bookHref ?? url('/#courses') }}"
       class="btn {{ $featured ? 'btn-primary' : 'btn-dark' }} book"
       data-course="{{ $title }}">Book this course</a>
  @endif
</div>
