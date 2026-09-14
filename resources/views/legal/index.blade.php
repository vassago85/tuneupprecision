<x-layouts.site
    title="Legal"
    description="Legal documents for Tune Up Precision — terms, privacy, shipping, and returns. Statutory supplier details as required by ECTA."
>

  <section>
    <div class="wrap legal">
      <div class="sec-head reveal">
        <span class="eyebrow">Legal</span>
        <h2>Legal &amp; compliance</h2>
        <p>The documents that govern this website, training bookings, merch orders and rifle-build estimates. Statutory contact details sit at the foot of this page.</p>
      </div>

      <article class="legal-prose reveal">
        <ul class="legal-doc-list">
          <li>
            <a href="{{ route('legal.terms') }}">Terms of use</a>
            <span>Site use, bookings, the shop, and rifle builds.</span>
          </li>
          <li>
            <a href="{{ route('legal.privacy') }}">Privacy Policy</a>
            <span>How we handle personal information under POPIA.</span>
          </li>
          <li>
            <a href="{{ route('legal.shipping') }}">Shipping Policy</a>
            <span>Merch dispatch, courier, risk, and what we never ship.</span>
          </li>
          <li>
            <a href="{{ route('legal.refunds') }}">Returns &amp; Refunds Policy</a>
            <span>Cooling-off, CPA returns, and how to send something back.</span>
          </li>
        </ul>

        <p>Bookings, coaching and general enquiries go through the <a href="{{ route('contact.create') }}">contact form</a>. That is the preferred route. The statutory address and email below are published because the law requires them.</p>
      </article>

      <x-legal.disclosure />
    </div>
  </section>

</x-layouts.site>
