<x-layouts.site
    title="Checkout"
    description="Checkout at Tune Up Precision. Pay by EFT. Prices include VAT. Delivery inside South Africa."
    robots="noindex, follow"
>

  <section>
    <div class="wrap checkout-grid">
      <div class="reveal">
        <span class="eyebrow">Checkout</span>
        <h2>Where should we send it?</h2>
        <p class="checkout-intro">Pay by EFT after you place the order. We dispatch once the payment reflects. Delivery is inside South Africa only.</p>

        @error('cart')
          <div class="auth-note info">{{ $message }}</div>
        @enderror

        <form method="POST" action="{{ route('shop.checkout.place') }}" class="auth-form contact-card" novalidate>
          @csrf
          <div class="nl-hp" aria-hidden="true">
            <label>Company (leave blank)
              <input type="text" name="company" tabindex="-1" autocomplete="off">
            </label>
          </div>

          <div class="form-field">
            <label for="customer_name">Name</label>
            <input id="customer_name" name="customer_name" value="{{ old('customer_name') }}" autocomplete="name" required>
            @error('customer_name')<div class="form-err">{{ $message }}</div>@enderror
          </div>

          <div class="checkout-pair">
            <div class="form-field">
              <label for="email">Email</label>
              <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
              @error('email')<div class="form-err">{{ $message }}</div>@enderror
            </div>
            <div class="form-field">
              <label for="phone">Phone</label>
              <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" required>
              @error('phone')<div class="form-err">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="form-field">
            <label for="address_line_1">Street address</label>
            <input id="address_line_1" name="address_line_1" value="{{ old('address_line_1') }}" autocomplete="address-line1" required>
            @error('address_line_1')<div class="form-err">{{ $message }}</div>@enderror
          </div>

          <div class="form-field">
            <label for="address_line_2">Complex, unit or building <span class="form-optional">optional</span></label>
            <input id="address_line_2" name="address_line_2" value="{{ old('address_line_2') }}" autocomplete="address-line2">
            @error('address_line_2')<div class="form-err">{{ $message }}</div>@enderror
          </div>

          <div class="checkout-pair">
            <div class="form-field">
              <label for="suburb">Suburb</label>
              <input id="suburb" name="suburb" value="{{ old('suburb') }}" required>
              @error('suburb')<div class="form-err">{{ $message }}</div>@enderror
            </div>
            <div class="form-field">
              <label for="city">City</label>
              <input id="city" name="city" value="{{ old('city') }}" autocomplete="address-level2" required>
              @error('city')<div class="form-err">{{ $message }}</div>@enderror
            </div>
          </div>

          <div class="checkout-pair">
            <div class="form-field">
              <label for="province">Province</label>
              <select id="province" name="province" required>
                <option value="">Select</option>
                @foreach ($provinces as $province)
                  <option value="{{ $province }}" @selected(old('province') === $province)>{{ $province }}</option>
                @endforeach
              </select>
              @error('province')<div class="form-err">{{ $message }}</div>@enderror
            </div>
            <div class="form-field">
              <label for="postal_code">Postal code</label>
              <input id="postal_code" name="postal_code" value="{{ old('postal_code') }}" inputmode="numeric" autocomplete="postal-code" maxlength="4" required>
              @error('postal_code')<div class="form-err">{{ $message }}</div>@enderror
            </div>
          </div>

          <label class="form-check">
            <input type="checkbox" name="terms" value="1" @checked(old('terms')) required>
            <span>I agree to the <a class="form-link" href="{{ route('legal.terms') }}">Terms</a>, <a class="form-link" href="{{ route('legal.privacy') }}">Privacy Policy</a>, <a class="form-link" href="{{ route('legal.shipping') }}">Shipping Policy</a> and <a class="form-link" href="{{ route('legal.refunds') }}">Returns &amp; Refunds</a>.</span>
          </label>
          @error('terms')<div class="form-err">{{ $message }}</div>@enderror

          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Place order</button>
        </form>
      </div>

      <aside class="checkout-summary reveal">
        <h3>Your order</h3>
        <ul class="summary-lines">
          @foreach ($lines as $line)
            <li>
              <span>{{ $line['product']->name }} <em>× {{ $line['qty'] }}</em></span>
              <span>{{ \App\Support\Money::format($line['line_cents']) }}</span>
            </li>
          @endforeach
        </ul>
        <dl class="summary-totals">
          <div><dt>Goods</dt><dd>{{ \App\Support\Money::format($cart->goodsCents()) }}</dd></div>
          <div><dt>Delivery</dt><dd>{{ $cart->shippingCents() === 0 ? 'Included' : \App\Support\Money::format($cart->shippingCents()) }}</dd></div>
          <div class="due"><dt>Total</dt><dd>{{ \App\Support\Money::format($cart->totalCents()) }}</dd></div>
          <div class="vat"><dt>Includes VAT</dt><dd>{{ \App\Support\Money::format($cart->vatCents()) }}</dd></div>
        </dl>
        <a class="form-link" href="{{ route('shop') }}">Continue shopping</a>
      </aside>
    </div>
  </section>

</x-layouts.site>
