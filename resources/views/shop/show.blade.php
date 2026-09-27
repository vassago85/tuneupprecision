@php($shareImage = $product->getFirstMediaUrl('images', 'web') ?: ($product->getFirstMediaUrl('images') ?: null))
@php($images = $product->getMedia('images'))
<x-layouts.site
    :title="$product->name"
    :description="$product->description ?: $product->name.' — Tune Up Precision shop. Price includes VAT.'"
    :image="$shareImage"
    type="product"
>

  <section class="product-page">
    <div class="wrap">
      <p class="shop-crumb reveal"><a href="{{ route('shop') }}">Shop</a>@if ($product->category) <span>/</span> <a href="{{ route('shop', ['category' => $product->category]) }}">{{ $product->category }}</a>@endif</p>

      <div class="product-layout reveal">
        <div class="product-gallery">
          @forelse ($images as $index => $image)
            <div class="product-stage">
              <img
                src="{{ $image->getUrl('web') ?: $image->getUrl() }}"
                alt="{{ $images->count() > 1 ? $product->name.', photo '.($index + 1).' of '.$images->count() : $product->name }}"
              >
            </div>
          @empty
            <div class="product-stage">
              <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="3"/><circle cx="12" cy="12" r="4"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>
            </div>
          @endforelse
        </div>

        <div class="product-buy">
          @if ($product->category)
            <span class="eyebrow">{{ $product->category }}</span>
          @endif
          <h1>{{ $product->name }}</h1>
          @if (filled($product->description))
            <p class="product-lead">{{ $product->description }}</p>
          @endif

          @php($cents = (int) $product->price_cents)
          <div class="product-price">{{ \App\Support\Money::format($cents, $cents % 100 !== 0) }}</div>

          <ul class="product-facts">
            <li>Includes {{ \App\Support\VatPrice::percentLabel() }} VAT</li>
            <li>In stock</li>
            <li>Delivery inside South Africa</li>
          </ul>

          <form method="POST" action="{{ route('shop.cart.add') }}" class="product-add">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <label class="qty">
              <span>Qty</span>
              <input type="number" name="qty" value="1" min="1" max="{{ $product->stock_qty }}" required>
            </label>
            <button class="btn btn-primary" type="submit">Add to cart</button>
          </form>
        </div>
      </div>
    </div>
  </section>

  @if ($related->isNotEmpty())
    <section class="product-more">
      <div class="wrap">
        <div class="sec-head reveal">
          <span class="eyebrow">{{ filled($product->category) ? $product->category : 'The kit shop' }}</span>
          <h2>{{ filled($product->category) ? 'More in '.$product->category : 'More from the shop' }}</h2>
        </div>
        <div class="shop spot-grid">
          @foreach ($related as $item)
            <x-shop.product-card :product="$item" />
          @endforeach
        </div>
      </div>
    </section>
  @endif

</x-layouts.site>
