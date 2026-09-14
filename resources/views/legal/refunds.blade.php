<x-layouts.site
    title="Returns & Refunds"
    description="Returns, cooling-off and refunds for Tune Up Precision merch, services and custom builds."
>

  @php
    $updated = \Illuminate\Support\Carbon::parse(config('legal.updated.refunds'))->format('d F Y');
  @endphp

  <section>
    <div class="wrap legal">
      <div class="sec-head reveal">
        <span class="eyebrow">Legal</span>
        <h2>Returns &amp; Refunds Policy</h2>
        <p>Last updated {{ $updated }}. This policy covers shop goods and electronic transactions. Course cancellations are in the <a href="{{ route('legal.terms') }}">Terms</a>.</p>
      </div>

      <article class="legal-prose reveal">
        <h3>1. Cooling-off (ECTA section 44)</h3>
        <p>If you are a consumer who concluded an electronic transaction with us, you may cancel without reason or penalty:</p>
        <ul>
          <li>for goods — within 7 days of receiving them;</li>
          <li>for services — within 7 days of concluding the agreement, if the service has not already begun with your consent.</li>
        </ul>
        <p>We refund you within 30 days of the cancellation. You bear the direct cost of returning the goods.</p>

        <h3>2. What cooling-off does not cover</h3>
        <p>The 7-day cooling-off right does not apply to:</p>
        <ul>
          <li>goods made to your specification — including custom rifle builds and chambering work;</li>
          <li>goods that cannot be returned by their nature;</li>
          <li>services that have already begun with your consent.</li>
        </ul>
        <p>That exclusion for custom and made-to-specification work is also stated in the <a href="{{ route('legal.terms') }}">Terms</a> (rifle builder and firearms).</p>

        <h3>3. Defective or not-as-described goods (CPA)</h3>
        <p>If goods are defective, unsafe or not as described, you have the rights in section 56 of the Consumer Protection Act 68 of 2008 — repair, replace or refund within 6 months of delivery. Nothing in this policy limits those rights.</p>

        <h3>4. How to return something</h3>
        <ol>
          <li>Use the <a href="{{ route('contact.create', ['subject' => 'Return authorisation']) }}">contact form</a> and ask for a return authorisation. Say what you bought, when, and why it is coming back.</li>
          <li>Wait for the authorisation before you post. We will give you the returns address.</li>
          <li>Return the goods unused where the reason is cooling-off, in the original packaging where you still have it, with all parts and paperwork.</li>
        </ol>
        <p>We may refuse a cooling-off return that is used, incomplete or not in a resaleable condition, except where the goods are defective.</p>

        <h3>5. Refund method and time</h3>
        <p>Refunds go back to the originating payment method, or to the originating bank account for EFT. Once we accept the return (or confirm a cancellation we must refund), we pay within 30 days.</p>

        <h3>6. Course bookings</h3>
        <p>Cancellation and refund rules for training dates are in the <a href="{{ route('legal.terms') }}">Terms</a> (bookings, cancellation and refunds). They are not duplicated here.</p>

        <h3>7. Disputes</h3>
        <p>Raise the issue with us first through the contact form. If we cannot resolve it, you may approach the National Consumer Commission.</p>
      </article>

      <x-legal.disclosure />
    </div>
  </section>

</x-layouts.site>
