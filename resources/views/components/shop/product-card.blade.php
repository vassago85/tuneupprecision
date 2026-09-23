@props([
    'product',
    'badge' => null,
])
{{-- Product card, extracted from the approved mockup (.prod). --}}
@php($image = $product->getFirstMediaUrl('images', 'thumb') ?: $product->getFirstMediaUrl('images'))
<div class="prod reveal spot">
  <a class="img" href="{{ route('shop.show', $product) }}">
    @if ($badge)
      <span class="badge">{{ $badge }}</span>
    @endif
    @if ($image)
      <img src="{{ $image }}" alt="{{ $product->name }}">
    @else
      {{-- Placeholder crosshair while no image is set. --}}
      <svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="3"/><circle cx="12" cy="12" r="4"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>
    @endif
  </a>
  <div class="body">
    <a class="prod-link" href="{{ route('shop.show', $product) }}">
      <span class="cat">{{ $product->category }}</span>
      <h3>{{ $product->name }}</h3>
    </a>
    @if (filled($product->description))
      <p class="blurb">{{ $product->description }}</p>
    @endif
    <div class="foot">
      @php($cents = (int) $product->price_cents)
      <span class="pr">{{ \App\Support\Money::format($cents, $cents % 100 !== 0) }}</span>
      <form method="POST" action="{{ route('shop.cart.add') }}">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <button class="add" type="submit">
          <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>Add
        </button>
      </form>
    </div>
  </div>
</div>
