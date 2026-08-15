<x-mail::message>
# Rifle build quotation {{ $quote->reference }}

Hi {{ $quote->customer_name }},

Please find your Tune Up Precision rifle-build quotation attached as a PDF.

- **Total incl. VAT:** {{ $quote->total }}
- **Valid until:** {{ optional($quote->valid_until)->format('d M Y') }}
- **Lead time:** {{ $quote->lead_time }}

A {{ $quote->deposit_percent }}% deposit is required to commence the build. Reply to this email or WhatsApp Dirk if you have questions.

Thanks,<br>
Tune Up Precision
</x-mail::message>
