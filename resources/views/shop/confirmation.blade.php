<x-layouts.site
    title="Order received"
    description="Your Tune Up Precision order is in. Pay by EFT using the order reference."
    robots="noindex, nofollow"
>

  <section>
    <div class="wrap confirm-wrap">
      <div class="confirm-card reveal">
        <span class="eyebrow">Order received</span>
        <h2>Pay by EFT, then we pack it.</h2>
        <p>Thanks, {{ $order->customer_name }}. Use this reference on the payment so we can match it.</p>

        <p class="confirm-ref">{{ $order->reference }}</p>

        <ul class="summary-lines">
          @foreach ($order->orderItems as $item)
            <li>
              <span>{{ $item->name_snapshot }} <em>× {{ $item->qty }}</em></span>
              <span>{{ $item->lineTotal }}</span>
            </li>
          @endforeach
        </ul>

        <dl class="summary-totals">
          <div><dt>Goods</dt><dd>{{ $order->subtotal }}</dd></div>
          <div><dt>Delivery</dt><dd>{{ (int) $order->shipping_cents === 0 ? 'Included' : \App\Support\Money::format((int) $order->shipping_cents) }}</dd></div>
          <div class="due"><dt>To pay</dt><dd>{{ $order->total }}</dd></div>
        </dl>

        <div class="eft-box">
          <h3>Bank details</h3>
          <dl>
            <div><dt>Bank</dt><dd>{{ $eft['bank_name'] }}</dd></div>
            <div><dt>Account name</dt><dd>{{ $eft['account_name'] }}</dd></div>
            <div><dt>Account number</dt><dd>{{ $eft['account_number'] }}</dd></div>
            <div><dt>Branch code</dt><dd>{{ $eft['branch_code'] }}</dd></div>
            <div><dt>Reference</dt><dd>{{ $order->reference }}</dd></div>
          </dl>
        </div>

        <p class="product-meta">Deliver to {{ $order->deliveryAddress() }}. A copy of this is on its way to {{ $order->email }}.</p>
        <a class="btn btn-ghost" href="{{ route('shop') }}">Back to the shop</a>
      </div>
    </div>
  </section>

</x-layouts.site>
