@php
    $course = $event?->courseTemplate?->title ?? 'Training';
    $when = $event?->starts_on?->format('D d M Y');
    $heldUntil = $booking->hold_expires_at?->timezone('Africa/Johannesburg')->format('D d M Y, H:i');
@endphp
<x-layouts.site
    title="Seat held"
    description="Your Tune Up Precision course seat is held. Pay by EFT using the booking reference."
    robots="noindex, nofollow"
>
  <section>
    <div class="wrap confirm-wrap">
      <div class="confirm-card reveal">
        <span class="eyebrow">Seat held</span>
        <h2>{{ $course }}</h2>
        <p>Thanks, {{ $booking->customer_name }}. {{ $booking->seats }} {{ $booking->seats === 1 ? 'seat is' : 'seats are' }} held for {{ $when }}@if ($event?->venue) at {{ $event->venue }}@endif. Use this reference on the payment so we can match it.</p>

        <p class="confirm-ref">{{ $booking->reference }}</p>

        @if ((int) $booking->amount_cents > 0)
          <dl class="summary-totals">
            <div class="due"><dt>To pay</dt><dd>{{ $booking->amount }}</dd></div>
          </dl>

          <div class="eft-box">
            <h3>Bank details</h3>
            <dl>
              <div><dt>Bank</dt><dd>{{ $eft['bank_name'] }}</dd></div>
              <div><dt>Account name</dt><dd>{{ $eft['account_name'] }}</dd></div>
              <div><dt>Account number</dt><dd>{{ $eft['account_number'] }}</dd></div>
              <div><dt>Branch code</dt><dd>{{ $eft['branch_code'] }}</dd></div>
              <div><dt>Reference</dt><dd>{{ $booking->reference }}</dd></div>
            </dl>
          </div>

          <p class="product-meta">The seat stays yours until {{ $heldUntil }} (Johannesburg). If payment has not arrived by then, it is released. A copy of this is on its way to {{ $booking->email }}.</p>
        @else
          <p class="product-meta">Dirk will confirm the amount for this date. The seat stays yours until {{ $heldUntil }} (Johannesburg). A copy of this is on its way to {{ $booking->email }}.</p>
        @endif

        <a class="btn btn-ghost" href="{{ route('courses') }}">Back to courses</a>
      </div>
    </div>
  </section>
</x-layouts.site>
