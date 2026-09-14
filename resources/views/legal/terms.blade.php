<x-layouts.site
    title="Terms"
    description="Terms of use for Tune Up Precision — training bookings, the website, shop, rifle builder and statutory contact details."
>

  @php
    $legal = \App\Support\LegalIdentity::effective();
    $name = $legal['trading_as'] ?: ($legal['legal_name'] ?: 'Tune Up Precision');
    $fullRefundDays = (int) data_get($legal, 'booking.full_refund_days', 14);
    $forfeitDays = (int) data_get($legal, 'booking.deposit_forfeit_days', 7);
    $vatPercent = (int) round(((float) ($legal['vat_rate'] ?? 0.15)) * 100);
    $updated = \Illuminate\Support\Carbon::parse(config('legal.updated.terms'))->format('d F Y');
  @endphp

  <section>
    <div class="wrap legal">
      <div class="sec-head reveal">
        <span class="eyebrow">Legal</span>
        <h2>Terms of use</h2>
        <p>Last updated {{ $updated }}. These terms govern use of tuneupprecision.co.za and training, coaching, products and rifle builds offered by {{ $name }} (“Tune Up Precision”, “we”, “us”) in {{ $legal['forum'] }}, {{ $legal['jurisdiction'] }}.</p>
      </div>

      <article class="legal-prose reveal">
        <h3>1. Contact</h3>
        <p>Bookings, coaching and general enquiries go through the <a href="{{ route('contact.create') }}">contact form</a>. That is the preferred route. A statutory physical address and email are published on the <a href="{{ route('legal.index') }}">legal page</a> as required by law. A reply from us may come by email to the address you entered on the form.</p>

        <h3>2. The website</h3>
        <p>We try to keep dates, prices and product information accurate. Course dates, seats left and prices can change. A listing on the site is an invitation to enquire, not a confirmed booking, until Dirk confirms the seat or the order in writing (including email).</p>
        <p>Do not misuse the site: no scraping that harms the service, no attempts to break in, and no automated form spam. Forms include a hidden honeypot field and a short timing check to reject bots.</p>

        <h3>3. Training and coaching</h3>
        <p>Courses are full days at a private facility. You bring your own rifle and ammunition unless we agree otherwise. Targets and use of ballistic and reloading kit on the day are included where the course description says so.</p>
        <p>You must be legally entitled to possess and use the firearm you bring. You must follow range commands and safety rules without argument. We may stop a session and refuse a refund if you are unsafe, intoxicated, or ignore instructions.</p>
        <p>Long range shooting and reloading carry inherent risk. You take part at your own risk. To the extent South African law allows, we are not liable for injury, death or loss arising from your shooting, your equipment, or your ammunition, except where caused by our gross negligence.</p>
        <p>One-on-one coaching is quoted individually after you describe what you need on the contact form.</p>

        <h3>4. Bookings, cancellation and refunds</h3>
        <p>A date is held only after we confirm it. Payment terms (deposit, EFT details, due dates) are given in that confirmation or on a formal quote. The deposit is the portion we confirm in writing when we accept the booking. Unpaid holds may be released.</p>
        <p>If you need to cancel or move a date, tell us through the contact form. The following applies unless the confirmation says otherwise:</p>
        <ul>
          <li><strong>More than {{ $fullRefundDays }} days’ notice</strong> — full refund of amounts paid, or a free transfer to another published date with space.</li>
          <li><strong>{{ $fullRefundDays }} days or fewer</strong> — the deposit is forfeited, or we may transfer it to another date at our discretion. It is not refunded as cash.</li>
          <li><strong>Fewer than {{ $forfeitDays }} days, or a no-show</strong> — no refund.</li>
          <li><strong>We cancel or postpone</strong> — full refund of amounts paid, or a transfer to a new date you accept.</li>
          <li><strong>Weather or range closure</strong> — we postpone. Your seat and payments move to the new date. If you cannot take that date, we refund in full.</li>
        </ul>
        <p>The deposit is refundable only when you cancel with more than {{ $fullRefundDays }} days’ notice, or when we cancel or postpone. Course-booking refunds are handled under this section, not as a shop return. See also the <a href="{{ route('legal.refunds') }}">Returns &amp; Refunds Policy</a>.</p>
        <p>Fully booked dates stay visible so you can see the calendar. A “Fully booked” listing is not a seat and is not a waitlist.</p>

        <h3>5. Shop</h3>
        <p>Shop prices include VAT at {{ $vatPercent }}%. Stock shown on the site is an indication, not a guarantee, until we confirm the order in writing. A sale concludes when we accept your order and confirm that payment has been received.</p>
        <p>Delivery cost, where it applies, is disclosed before the transaction concludes. Merch shipping and returns are set out in the <a href="{{ route('legal.shipping') }}">Shipping Policy</a> and the <a href="{{ route('legal.refunds') }}">Returns &amp; Refunds Policy</a>.</p>

        <h3>6. Rifle builder and firearms</h3>
        <p>The rifle builder produces an estimate, not an offer or a quote. A formal quote is issued separately, in writing, and is the document that can be accepted.</p>
        <p>No firearm, frame, receiver or barrelled action is sold, transferred or delivered through this website. All such transfers are completed in person through a licensed dealer under the Firearms Control Act 60 of 2000, against the buyer’s valid licence, competency and the applicable SAPS permits and registers.</p>
        <p>We may refuse or cancel any build or sale we cannot lawfully complete. If you have already paid for a transfer that cannot be completed, we refund that amount.</p>
        <p>Lead times are estimates. They are not a term of the agreement.</p>
        <p>Custom and made-to-specification builds — including chambering work done to your specification — are excluded from the 7-day cooling-off right under section 44(2) of the Electronic Communications and Transactions Act 25 of 2002. That is said again in the <a href="{{ route('legal.refunds') }}">Returns &amp; Refunds Policy</a>.</p>

        <h3>7. Member accounts and videos</h3>
        <p>If you create an account, keep the password to yourself. Gated videos are for the account holder. We may close an account that is shared, abused, or used to copy content.</p>

        <h3>8. Newsletter</h3>
        <p>Subscribe only if you want the monthly note. You can unsubscribe from the link in any newsletter.</p>

        <h3>9. Intellectual property</h3>
        <p>Site copy, photos, videos, marks and course materials belong to Tune Up Precision or their licensors. You may not copy them for a competing course or commercial use without written permission.</p>

        <h3>10. Privacy</h3>
        <p>How we handle personal information is set out in the <a href="{{ route('legal.privacy') }}">Privacy Policy</a>.</p>

        <h3>11. Consumer rights preserved</h3>
        <p>Nothing in these terms limits any right you have under the Consumer Protection Act 68 of 2008 or the Electronic Communications and Transactions Act 25 of 2002.</p>

        <h3>12. Changes and law</h3>
        <p>We may update these terms. The date at the top is the latest version. South African law applies. If a dispute cannot be resolved through the contact form, the courts of South Africa have jurisdiction, with Gauteng as the preferred forum. Consumer complaints may also be taken to the National Consumer Commission.</p>
      </article>

      <x-legal.disclosure />
    </div>
  </section>

</x-layouts.site>
