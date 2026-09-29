<x-mail::message>
@php
    $course = $event?->courseTemplate?->title ?? 'Training';
    $when = $event?->starts_on?->format('D d M Y');
    $heldUntil = $booking->hold_expires_at?->timezone('Africa/Johannesburg')->format('D d M Y, H:i');
@endphp
@if ($forCustomer)
# Your seat is held

Hi {{ $booking->customer_name }},

**{{ $course }}** on **{{ $when }}** is held for you — {{ $booking->seats }} {{ $booking->seats === 1 ? 'seat' : 'seats' }}.

**Reference:** {{ $booking->reference }}

@if ((int) $booking->amount_cents > 0)
Pay **{{ $booking->amount }}** by EFT and use that reference so we can match the payment.

- **Bank:** {{ $eft['bank_name'] }}
- **Account name:** {{ $eft['account_name'] }}
- **Account number:** {{ $eft['account_number'] }}
- **Branch code:** {{ $eft['branch_code'] }}

The seat stays yours until **{{ $heldUntil }}** (Johannesburg). If payment has not arrived by then, the seat is released.
@else
Dirk will confirm the amount for this date. The seat stays yours until **{{ $heldUntil }}** (Johannesburg).
@endif

@if ($booking->rifle)
Rifle: {{ $booking->rifle }}
@endif
@else
# New course booking

{{ $booking->customer_name }} reserved {{ $booking->seats }} {{ $booking->seats === 1 ? 'seat' : 'seats' }} on **{{ $course }}** ({{ $when }}).

- **Reference:** {{ $booking->reference }}
- **Email:** {{ $booking->email }}
- **Phone:** {{ $booking->phone }}
- **Rifle:** {{ $booking->rifle ?: '—' }}
- **Amount:** {{ $booking->amount }}
- **Hold until:** {{ $heldUntil }} SAST

Reply to this email to reach them. Mark the booking paid in admin once the EFT lands — that keeps the seat after the hold.
@endif

Thanks,<br>
Tune Up Precision
</x-mail::message>
