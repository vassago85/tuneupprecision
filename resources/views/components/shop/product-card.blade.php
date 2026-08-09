@props([
    'product',
    'badge' => null,
])
{{-- Product card, extracted from the approved mockup (.prod). --}}
@php($image = $product->getFirstMediaUrl('images', 'thumb') ?: $product->getFirstMediaUrl('images'))
<div class="prod reveal">
  <div class="img">
    @if ($badge)
      <span class="badge">{{ $badge }}</span>
    @endif
    @if ($image)
      <img src="{{ $image }}" alt="{{ $product->name }}">
    @else
      {{-- Placeholder crosshair while no image is set. --}}
      <svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="3"/><circle cx="12" cy="12" r="4"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3"/></svg>
    @endif
  </div>
  <div class="body">
    <span class="cat">{{ $product->category }}</span>
    <h3>{{ $product->name }}</h3>
    <div class="foot">
      <span class="pr">{{ \App\Support\Money::format((int) $product->price_cents, false) }}</span>
      <button class="add" type="button" data-name="{{ $product->name }}">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>Add
      </button>
    </div>
  </div>
</div>
