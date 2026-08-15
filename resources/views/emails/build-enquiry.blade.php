<x-mail::message>
@if ($forCustomer)
# We received your build request

Hi {{ $quote->customer_name }},

Thanks for speccing a rifle with Tune Up Precision. Dirk will review **{{ $quote->reference }}** and come back to you with a formal quotation.

@else
# New rifle build enquiry

{{ $quote->customer_name }} requested a custom build.

- **Email:** {{ $quote->customer_email }}
- **Phone:** {{ $quote->customer_phone ?: '—' }}
- **Licence:** {{ $quote->licence_status ?: '—' }}
- **Reference:** {{ $quote->reference }}

@endif

**Platform:** {{ $quote->platform->getLabel() }}
@if ($quote->chambering)
**Chambering:** {{ $quote->chambering }} · {{ $quote->barrel_length }} · {{ $quote->barrel_twist }}
@endif

@foreach ($quote->lines as $line)
- {{ $line->group_label }}: {{ $line->brand }} {{ $line->description }}@if ($line->quantity > 1) ×{{ $line->quantity }}@endif — {{ $line->line_total }}
@endforeach

**Total incl. VAT:** {{ $quote->total }}
**Lead time:** {{ $quote->lead_time }}

@if ($quote->notes)
**Message:** {{ $quote->notes }}
@endif

Thanks,<br>
Tune Up Precision
</x-mail::message>
