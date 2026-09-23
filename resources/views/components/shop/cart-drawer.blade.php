@php
    $cart = app(\App\Shop\Cart::class);
    $lines = $cart->lines();
    $open = session('cart_open') || $errors->has('cart');
@endphp

<div class="cart-drawer {{ $open ? 'open' : '' }}" id="cartDrawer" data-open="{{ $open ? '1' : '0' }}" aria-hidden="{{ $open ? 'false' : 'true' }}">
  <button class="cart-backdrop" type="button" data-cart-close aria-label="Close cart"></button>
  <aside class="cart-panel" role="dialog" aria-labelledby="cartTitle">
    <header class="cart-head">
      <h2 id="cartTitle">Cart</h2>
      <button type="button" data-cart-close aria-label="Close cart">
        <svg viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </header>

    @if (session('cart_error') || $errors->has('cart'))
      <p class="cart-note">{{ session('cart_error') ?: $errors->first('cart') }}</p>
    @endif

    @if ($lines->isEmpty())
      <p class="cart-empty">Your cart is empty.</p>
      <a class="btn btn-primary" href="{{ route('shop') }}" data-cart-close>Browse the shop</a>
    @else
      <ul class="cart-lines">
        @foreach ($lines as $line)
          <li>
            <div>
              <a href="{{ route('shop.show', $line['product']) }}">{{ $line['product']->name }}</a>
              <span>{{ \App\Support\Money::format((int) $line['product']->price_cents) }} each</span>
            </div>
            <form method="POST" action="{{ route('shop.cart.update', $line['product']) }}" class="cart-qty">
              @csrf
              @method('PATCH')
              <input type="number" name="qty" value="{{ $line['qty'] }}" min="0" max="{{ $line['product']->stock_qty }}" aria-label="Quantity for {{ $line['product']->name }}">
              <button type="submit">Update</button>
            </form>
            <strong>{{ \App\Support\Money::format($line['line_cents']) }}</strong>
            <form method="POST" action="{{ route('shop.cart.remove', $line['product']) }}">
              @csrf
              @method('DELETE')
              <button type="submit" class="cart-remove">Remove</button>
            </form>
          </li>
        @endforeach
      </ul>

      <dl class="summary-totals cart-totals">
        <div><dt>Goods</dt><dd>{{ \App\Support\Money::format($cart->goodsCents()) }}</dd></div>
        <div><dt>Delivery</dt><dd>{{ $cart->shippingCents() === 0 ? 'Included' : \App\Support\Money::format($cart->shippingCents()) }}</dd></div>
        <div class="due"><dt>Total</dt><dd>{{ \App\Support\Money::format($cart->totalCents()) }}</dd></div>
        <div class="vat"><dt>Includes VAT</dt><dd>{{ \App\Support\Money::format($cart->vatCents()) }}</dd></div>
      </dl>

      <a class="btn btn-primary" href="{{ route('shop.checkout') }}">Checkout</a>
    @endif
  </aside>
</div>
