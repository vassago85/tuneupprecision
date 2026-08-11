<x-layouts.site title="Shop">

  {{-- ============ SHOP ============ --}}
  <section>
    <div class="wrap">
      <div class="sec-head reveal">
        <span class="eyebrow">The kit shop</span>
        <h2>Gear that earns its place.</h2>
        <p>Merch and range essentials, shipped countrywide. More stock added between intakes.</p>
      </div>

      <div class="shop">
        @forelse ($products as $product)
          <x-shop.product-card :product="$product" />
        @empty
          <p class="mono" style="color:var(--muted)">New stock lands between intakes — check back soon.</p>
        @endforelse
      </div>
    </div>
  </section>

  <x-site.cta-band />

</x-layouts.site>
