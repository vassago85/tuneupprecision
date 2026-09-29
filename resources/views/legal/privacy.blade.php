<x-layouts.site
    title="Privacy Policy"
    description="How Tune Up Precision collects, uses and protects personal information under POPIA."
>

  @php
    $legal = \App\Support\LegalIdentity::effective();
    $responsible = $legal['legal_name'] ?: 'Tune Up Precision';
    if ($legal['trading_as'] && $legal['trading_as'] !== $legal['legal_name']) {
        $responsible .= ', trading as '.$legal['trading_as'];
    }
    $officer = $legal['office_bearers'] ?: $responsible;
    $updated = \Illuminate\Support\Carbon::parse(config('legal.updated.privacy'))->format('d F Y');
  @endphp

  <section>
    <div class="wrap legal">
      <div class="sec-head reveal">
        <span class="eyebrow">Legal</span>
        <h2>Privacy Policy</h2>
        <p>Last updated {{ $updated }}. {{ $responsible }} (“Tune Up Precision”, “we”, “us”) is based in {{ $legal['forum'] }}, {{ $legal['jurisdiction'] }}. This policy explains how we handle personal information under the Protection of Personal Information Act 4 of 2013 (POPIA).</p>
      </div>

      <article class="legal-prose reveal">
        <h3>1. Who is responsible</h3>
        <p>{{ $responsible }} is the responsible party.</p>
        @if ($legal['physical_address'])
          <p>Statutory physical address: {{ $legal['physical_address'] }}.</p>
        @endif
        @if ($legal['legal_email'])
          <p>Statutory email: <a href="mailto:{{ $legal['legal_email'] }}">{{ $legal['legal_email'] }}</a>.</p>
        @endif
        <p>The information officer is {{ $officer }}. You can reach them about this policy, or to exercise your rights, through the <a href="{{ \App\Support\ContactLink::url('Privacy request') }}">contact form</a> or, where published, the statutory email above. The same details appear on the <a href="{{ route('legal.index') }}">legal page</a>.</p>

        <h3>2. What we collect</h3>
        <p>We only collect what we need to run the site and the training:</p>
        <ul>
          <li><strong>Contact enquiries</strong> — name, email, optional phone number, subject and message.</li>
          <li><strong>Newsletter</strong> — email address, and a name if you give one.</li>
          <li><strong>Bookings</strong> — the details you send so we can confirm a seat or hold a date.</li>
          <li><strong>Member accounts</strong> — name, email and password if you register to access gated videos.</li>
          <li><strong>Rifle builder and shop</strong> — build specs, quote details, and the contact details you submit with a request.</li>
          <li><strong>Technical data</strong> — IP address, browser type and basic server logs needed to keep the site secure and working.</li>
          <li><strong>Site analytics</strong> — pages viewed, device and browser type, and an approximate location, collected by Google Analytics.</li>
        </ul>
        <p>We do not ask for identity numbers, firearm licence numbers or other special personal information on the public forms unless you choose to include them in a message. Please do not send copies of licences or other official documents through the contact form.</p>

        <h3>3. Why we use it</h3>
        <p>We process personal information to:</p>
        <ul>
          <li>reply to enquiries and book training;</li>
          <li>send the monthly newsletter if you asked for it;</li>
          <li>issue quotes, confirm payments and deliver products or builds;</li>
          <li>operate member accounts and gated videos;</li>
          <li>see which pages are used, so we can improve the site;</li>
          <li>keep the site secure and prevent spam (including a hidden honeypot field and a short timing check on forms);</li>
          <li>meet legal, tax and safety obligations that apply to a South African training and firearms-related business.</li>
        </ul>
        <p>The legal bases we rely on are your consent (newsletter, optional fields), performance of a contract or taking steps at your request (bookings, quotes, contact replies), and our legitimate interests in running a safe site and understanding how it is used.</p>

        <h3>4. Who we share it with</h3>
        <p>We do not sell personal information. We share it only with operators who help us run the service — email delivery, hosting, Google Analytics, and payment or banking channels you use to pay us. Those operators may process data outside South Africa; we expect them to protect it to a standard comparable with POPIA. Google’s own privacy policy is at <a href="https://policies.google.com/privacy" rel="noopener noreferrer" target="_blank">policies.google.com/privacy</a>.</p>
        <p>We may disclose information if a South African law or court requires it, or to protect someone’s safety on the range.</p>

        <h3>5. How long we keep it</h3>
        <p>Contact messages are kept for as long as needed to deal with the enquiry and a short period afterwards for follow-up. Newsletter addresses stay on the list until you unsubscribe. Booking, quote and payment records are kept for the period required for tax and dispute purposes. Member accounts stay until you ask us to close them.</p>

        <h3>6. Security</h3>
        <p>We protect personal information with TLS on the public site, hashed passwords for member accounts, access control so only authorised people reach admin tools, and retention limits so we do not keep data longer than we need it. No method of transmission or storage is perfectly secure; we take reasonable steps required of a responsible party under POPIA.</p>

        <h3>7. Cookies and analytics</h3>
        <p>The site uses a session cookie so forms, the shop cart and sign-in work. That cookie is needed to run the site.</p>
        <p>We use Google Analytics to see how the public site is used. It sets its own cookies (such as <code>_ga</code>) and sends usage data to Google: pages opened, device and browser type, and an approximate location. We use that to improve the site. We do not use it to advertise to you.</p>
        <p>Range videos load from YouTube only after you press play. Until then the player uses YouTube’s privacy-enhanced embed, which does not set a YouTube cookie just by opening the page.</p>
        <p>You can block these cookies in your browser, or install Google’s opt-out add-on: <a href="https://tools.google.com/dlpage/gaoptout" rel="noopener noreferrer" target="_blank">tools.google.com/dlpage/gaoptout</a>.</p>

        <h3>8. Your rights</h3>
        <p>You may request access to the personal information we hold about you, ask us to correct it, object to certain processing, withdraw newsletter consent, or ask us to delete it where we have no lawful reason to keep it. Use the <a href="{{ \App\Support\ContactLink::url('Privacy request') }}">contact form</a> and we will respond within a reasonable time.</p>
        <p>You may also lodge a complaint with the Information Regulator (South Africa): <a href="https://inforegulator.org.za" rel="noopener noreferrer" target="_blank">inforegulator.org.za</a>.</p>

        <h3>9. Children</h3>
        <p>The site is aimed at adults. We do not knowingly collect personal information from children under 18. Training involving a minor is arranged by a parent or guardian through the contact form.</p>

        <h3>10. Changes</h3>
        <p>We may update this policy when the site or the law changes. The date at the top is the latest version. Continued use of the site after an update means you have read the new version.</p>
      </article>

      <x-legal.disclosure />
    </div>
  </section>

</x-layouts.site>
