@props(['compact' => false])

@php
    $legal = \App\Support\LegalIdentity::effective();
    $compactParts = array_values(array_filter([
        $legal['legal_name'],
        $legal['registration_no'] ? 'Reg '.$legal['registration_no'] : null,
        $legal['vat_no'] ? 'VAT '.$legal['vat_no'] : null,
        $legal['physical_address'],
    ]));
    $website = rtrim((string) config('app.url'), '/');
@endphp

@if ($compact)
  @if ($compactParts !== [])
    <p {{ $attributes->class(['legal-disclosure-compact']) }}>{{ implode(' · ', $compactParts) }}</p>
  @endif
@else
  <aside {{ $attributes->class(['legal-disclosure']) }}>
    <h3>Supplier details</h3>
    <p>These details are published as required by section 43 of the Electronic Communications and Transactions Act 25 of 2002.</p>
    <ul>
      @if ($legal['legal_name'])
        <li><strong>Legal name</strong> — {{ $legal['legal_name'] }}</li>
      @endif
      @if ($legal['trading_as'])
        <li><strong>Trading as</strong> — {{ $legal['trading_as'] }}</li>
      @endif
      @if ($legal['legal_status'])
        <li><strong>Legal status</strong> — {{ $legal['legal_status'] }}</li>
      @endif
      @if ($legal['registration_no'])
        <li><strong>Registration number</strong> — {{ $legal['registration_no'] }}@if ($legal['jurisdiction']), {{ $legal['jurisdiction'] }}@endif</li>
      @endif
      @if ($legal['office_bearers'])
        <li><strong>Office bearers</strong> — {{ $legal['office_bearers'] }}</li>
      @endif
      @if ($legal['physical_address'])
        <li><strong>Physical address</strong> — {{ $legal['physical_address'] }}</li>
      @endif
      @if ($legal['postal_address'])
        <li><strong>Postal address</strong> — {{ $legal['postal_address'] }}</li>
      @endif
      @if ($legal['legal_phone'])
        <li><strong>Telephone</strong> — {{ $legal['legal_phone'] }}</li>
      @endif
      @if ($legal['legal_email'])
        <li><strong>Email</strong> — <a href="mailto:{{ $legal['legal_email'] }}">{{ $legal['legal_email'] }}</a></li>
      @endif
      @if ($website !== '')
        <li><strong>Website</strong> — <a href="{{ $website }}">{{ $website }}</a></li>
      @endif
      @if ($legal['vat_no'])
        <li><strong>VAT number</strong> — {{ $legal['vat_no'] }}</li>
      @endif
      @if ($legal['dealer_licence_no'])
        <li><strong>Firearms dealer licence</strong> — {{ $legal['dealer_licence_no'] }}</li>
      @endif
    </ul>
    <p>
      <a href="{{ route('legal.terms') }}">Terms</a>
      · <a href="{{ route('legal.privacy') }}">Privacy Policy</a>
      · <a href="{{ route('legal.shipping') }}">Shipping Policy</a>
      · <a href="{{ route('legal.refunds') }}">Returns &amp; Refunds Policy</a>
    </p>
    <p>Disputes go first through the <a href="{{ route('contact.create', ['subject' => 'Dispute']) }}">contact form</a>. If we cannot resolve the matter, consumer complaints may be taken to the National Consumer Commission, and remaining disputes to the courts of {{ $legal['jurisdiction'] }}, with {{ $legal['forum'] }} as the preferred forum.</p>
  </aside>
@endif
