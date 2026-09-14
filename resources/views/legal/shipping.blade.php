<x-layouts.site
    title="Shipping Policy"
    description="How Tune Up Precision dispatches merch in South Africa — courier, cost, risk, and what is never shipped."
>

  @php
    $legal = \App\Support\LegalIdentity::effective();
    $dispatch = data_get($legal, 'shipping.dispatch_business_days', '3–5');
    $courier = data_get($legal, 'shipping.courier', 'a South African courier');
    $updated = \Illuminate\Support\Carbon::parse(config('legal.updated.shipping'))->format('d F Y');
  @endphp

  <section>
    <div class="wrap legal">
      <div class="sec-head reveal">
        <span class="eyebrow">Legal</span>
        <h2>Shipping Policy</h2>
        <p>Last updated {{ $updated }}. This policy covers merch and other goods sold through the shop. It does not cover firearms or controlled components.</p>
      </div>

      <article class="legal-prose reveal">
        <h3>1. Where we deliver</h3>
        <p>We deliver inside South Africa only. We do not ship internationally.</p>

        <h3>2. Dispatch</h3>
        <p>In-stock merch is handed to the courier within {{ $dispatch }} business days of us confirming payment, unless the product page or your confirmation says otherwise. Weekend and public-holiday orders start the clock on the next business day.</p>

        <h3>3. Courier and estimates</h3>
        <p>We use {{ $courier }}. Main centres typically take 1–3 business days after dispatch. Outlying areas typically take 3–7 business days after dispatch. These are estimates, not guarantees. You receive a tracking reference when the parcel is collected.</p>

        <h3>4. Shipping cost</h3>
        <p>Shipping cost is calculated and shown before you complete checkout, so the full price including transport is disclosed before the transaction concludes, as required by ECTA section 43. If we ever take an order by email or EFT outside checkout, the confirmation will state the delivery charge before we ask you to pay.</p>

        <h3>5. Risk and ownership</h3>
        <p>Risk and ownership of the goods pass to you on delivery to the address you gave. Until then, the parcel is our responsibility or the courier’s under their conditions of carriage.</p>

        <h3>6. Lost or damaged parcels</h3>
        <p>If a parcel is lost, or arrives damaged, tell us through the <a href="{{ route('contact.create', ['subject' => 'Shipping problem']) }}">contact form</a> within 7 days of the delivery date (or the date it should have arrived). Keep the packaging and take photographs. We will raise the claim with the courier and repair, replace or refund the goods once the claim is settled.</p>

        <h3>7. Firearms and controlled components</h3>
        <p>Firearms and controlled components are never couriered to a buyer. Transfers happen in person through a licensed dealer. That rule is set out in the <a href="{{ route('legal.terms') }}">Terms</a> (rifle builder and firearms).</p>

        <h3>8. Returns</h3>
        <p>How to send goods back, cooling-off, and refunds are in the <a href="{{ route('legal.refunds') }}">Returns &amp; Refunds Policy</a>.</p>
      </article>

      <x-legal.disclosure />
    </div>
  </section>

</x-layouts.site>
