@php
    use App\Support\Money;

    $course = $event->courseTemplate;
    $dateLabel = $event->starts_on?->format('D d M Y');
    if ($event->ends_on && $event->ends_on->ne($event->starts_on)) {
        $dateLabel = $event->starts_on->format('D d M').' – '.$event->ends_on->format('D d M Y');
    }
    $priceCents = $event->effectivePriceCents();
    $seatsLeft = $event->seatsLeft();
@endphp
<x-layouts.site
    :title="'Book '.$course?->title"
    :description="'Reserve a seat on '.$course?->title.' — '.$dateLabel.'. Tune Up Precision.'"
    :canonical="route('bookings.create', $event)"
    robots="noindex, follow"
>
  <section>
    <div class="wrap contact-grid">
      <div class="contact-copy reveal">
        <p class="shop-crumb"><a href="{{ route('courses') }}">Courses</a>@if ($course) <span>/</span> <a href="{{ route('courses.show', $course) }}">{{ $course->title }}</a>@endif</p>
        <span class="eyebrow">{{ $course?->trainingType?->name }}</span>
        <h2>Book {{ $course?->title }}.</h2>
        <p>{{ $dateLabel }}@if ($event->venue) · {{ $event->venue }}@endif. {{ $seatsLeft }} of {{ $event->capacity }} seats left.</p>
        @if ($priceCents > 0)
          <p><strong>{{ Money::format($priceCents, false) }}</strong> per shooter. Submitting this form holds the seat for {{ $holdHours }} hours. Pay by EFT with the booking reference, or the seat is released.</p>
        @else
          <p>Price for this date is confirmed by Dirk. Submitting this form holds the seat for {{ $holdHours }} hours.</p>
        @endif
        <p class="soft">You bring your own firearm and ammunition. Read the <a href="{{ route('legal.terms') }}">Terms</a> for cancellation and refunds.</p>
      </div>

      <div class="contact-card reveal">
        <form method="POST" action="{{ route('bookings.store', $event) }}" class="auth-form" novalidate>
          @csrf
          <div class="nl-hp" aria-hidden="true">
            <label>Company (leave blank)
              <input type="text" name="company" tabindex="-1" autocomplete="off">
            </label>
          </div>
          <input type="hidden" name="ts" value="{{ time() }}">

          <div class="form-field">
            <label for="customer_name">Name</label>
            <input id="customer_name" type="text" name="customer_name" value="{{ old('customer_name') }}" autocomplete="name" required>
            @error('customer_name')<div class="form-err">{{ $message }}</div>@enderror
          </div>

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

          <div class="form-field">
            <label for="rifle">Rifle <span class="form-hint" style="display:inline;text-transform:none;letter-spacing:0;font-weight:400">(optional)</span></label>
            <input id="rifle" type="text" name="rifle" value="{{ old('rifle') }}" placeholder="What you're bringing">
            @error('rifle')<div class="form-err">{{ $message }}</div>@enderror
          </div>

          <div class="form-field">
            <label for="seats">Seats</label>
            @if ($seatsLeft > 1)
              <select id="seats" name="seats" required>
                @for ($i = 1; $i <= $seatsLeft; $i++)
                  <option value="{{ $i }}" @selected((int) old('seats', 1) === $i)>{{ $i }}</option>
                @endfor
              </select>
            @else
              <input id="seats" type="text" value="1" readonly>
              <input type="hidden" name="seats" value="1">
            @endif
            @error('seats')<div class="form-err">{{ $message }}</div>@enderror
          </div>

          <label class="form-field" style="flex-direction:row;align-items:flex-start;gap:10px">
            <input type="checkbox" name="terms" value="1" @checked(old('terms')) required style="margin-top:3px">
            <span style="font-family:var(--mono);font-size:12px;letter-spacing:0;text-transform:none;font-weight:400;color:var(--muted)">I agree to the <a class="form-link" href="{{ route('legal.terms') }}">Terms</a>, including the booking cancellation rules, and the <a class="form-link" href="{{ route('legal.privacy') }}">Privacy Policy</a>.</span>
          </label>
          @error('terms')<div class="form-err">{{ $message }}</div>@enderror

          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Reserve this seat</button>
        </form>
      </div>
    </div>
  </section>
</x-layouts.site>
