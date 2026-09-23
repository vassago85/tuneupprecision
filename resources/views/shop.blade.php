<x-layouts.site
    title="Shop"
    description="Range kit and merch from Tune Up Precision. Prices include VAT. Delivery inside South Africa."
>

  <section>
    <div class="wrap">
      <div class="shop-top reveal">
        <div class="sec-head">
          <span class="eyebrow">The kit shop</span>
          <h2>Gear that earns its place.</h2>
          <p>Merch and range essentials, shipped countrywide. Prices include VAT.</p>
        </div>
      </div>

      @if ($categories->isNotEmpty())
        <div class="type-filter reveal">
          <a href="{{ route('shop') }}" @class(['active' => $category === null])>All</a>
          @foreach ($categories as $name)
            <a href="{{ route('shop', ['category' => $name]) }}" @class(['active' => $category === $name])>{{ $name }}</a>
          @endforeach
        </div>
      @endif

      <div class="shop spot-grid">
        @forelse ($products as $product)
          <x-shop.product-card :product="$product" />
        @empty
          <p class="mono shop-empty">@if ($category) Nothing in {{ $category }} right now. @else New stock lands between intakes — check back soon. @endif</p>
        @endforelse
      </div>
    </div>
  </section>

  <x-site.cta-band />

</x-layouts.site>
