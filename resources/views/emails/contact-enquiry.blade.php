<x-mail::message>
@if ($forVisitor)
# We received your message

Hi {{ $enquiry['name'] }},

Thanks for getting in touch with Tune Up Precision. Dirk will reply to **{{ $enquiry['email'] }}** as soon as he can.

**Subject:** {{ $enquiry['subject'] }}

@else
# New website enquiry

Someone used the contact form on tuneupprecision.co.za.

- **Name:** {{ $enquiry['name'] }}
- **Email:** {{ $enquiry['email'] }}
- **Phone:** {{ $enquiry['phone'] ?: '—' }}
- **Subject:** {{ $enquiry['subject'] }}

**Message:**

{{ $enquiry['message'] }}

Reply directly to this email to reach them.
@endif

Thanks,<br>
Tune Up Precision
</x-mail::message>
