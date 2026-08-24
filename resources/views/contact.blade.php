<x-layouts.site
    title="Contact"
    description="Message Dirk at Tune Up Precision — book a course date, ask about one-on-one coaching, or enquire about a rifle build. All contact goes through this form."
>

  <section>
    <div class="wrap contact-grid">
      <div class="contact-copy reveal">
        <span class="eyebrow">Get in touch</span>
        <h2>Message Dirk through the site.</h2>
        <p>Bookings, coaching, builds and general questions all come in here. There is no public email or phone on the site — this form is the only way to reach Tune Up Precision.</p>
        <ul class="contact-points">
          <li>Course dates and waitlists</li>
          <li>One-on-one coaching</li>
          <li>Rifle builder and shop questions</li>
        </ul>
        <p class="soft">We reply as soon as we can. Read how we handle your details in the <a href="{{ route('legal.privacy') }}">Privacy Policy</a>.</p>
      </div>

      <div class="contact-card reveal">
        @if (session('contact_status') === 'success')
          <div class="auth-note ok">Thanks — your message is in. Dirk will reply to the email you gave.</div>
        @endif

        <form method="POST" action="{{ route('contact.store') }}" class="auth-form" novalidate>
          @csrf

          {{-- Honeypot: hidden from people, bots tend to fill it. --}}
          <div class="nl-hp" aria-hidden="true">
            <label>Company (leave blank)
              <input type="text" name="company" tabindex="-1" autocomplete="off">
            </label>
          </div>
          <input type="hidden" name="ts" value="{{ time() }}">

          <div class="form-field">
            <label for="name">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" autocomplete="name" required>
            @error('name')<div class="form-err">{{ $message }}</div>@enderror
          </div>

          <div class="form-field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
            @error('email')<div class="form-err">{{ $message }}</div>@enderror
          </div>

          <div class="form-field">
            <label for="phone">Phone <span class="form-hint" style="display:inline;text-transform:none;letter-spacing:0;font-weight:400">(optional)</span></label>
            <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel">
            @error('phone')<div class="form-err">{{ $message }}</div>@enderror
          </div>

          <div class="form-field">
            <label for="subject">Subject</label>
            <input id="subject" type="text" name="subject" value="{{ old('subject', $subject) }}" required>
            @error('subject')<div class="form-err">{{ $message }}</div>@enderror
          </div>

          <div class="form-field">
            <label for="message">Message</label>
            <textarea id="message" name="message" rows="6" required>{{ old('message') }}</textarea>
            @error('message')<div class="form-err">{{ $message }}</div>@enderror
          </div>

          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Send message</button>
          <p class="form-hint" style="text-align:center">By sending this you agree to the <a class="form-link" href="{{ route('legal.terms') }}">Terms</a> and <a class="form-link" href="{{ route('legal.privacy') }}">Privacy Policy</a>.</p>
        </form>
      </div>
    </div>
  </section>

</x-layouts.site>
