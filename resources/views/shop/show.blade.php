<x-layouts.site
    :title="$product->name"
    :description="$product->description ?: $product->name.' — Tune Up Precision shop. Price includes VAT.'"
>

  <section>
    <div class="wrap">
      <p class="shop-crumb reveal"><a href="{{ route('shop') }}">Shop</a>@if ($product->category) <span>/</span> <a href="{{ route('shop', ['category' => $product->category]) }}">{{ $product->category }}</a>@endif</p>

      <div class="product-layout reveal">
        <div class="product-stage">
          @php($image = $product->getFirstMediaUrl('images', 'web') ?: $product->getFirstMediaUrl('images'))
          @if ($image)
            <img src="{{ $image }}" alt="{{ $product->name }}">
          @else
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="3"/><circle cx="12" cy="12" r="4"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>
          @endif
        </div>

        <div class="product-buy">
          @if ($product->category)
            <span class="eyebrow">{{ $product->category }}</span>
          @endif
          <h2>{{ $product->name }}</h2>
          @if (filled($product->description))
            <p class="product-lead">{{ $product->description }}</p>
          @endif

          @php($cents = (int) $product->price_cents)
          <div class="product-price">{{ \App\Support\Money::format($cents, $cents % 100 !== 0) }}</div>
          <p class="product-meta">Includes {{ \App\Support\VatPrice::percentLabel() }} VAT · In stock · Delivery inside South Africa</p>

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

</x-layouts.site>
