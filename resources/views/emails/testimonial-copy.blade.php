<x-mail::message>
@if ($forVisitor)
# We received your testimonial

Hi {{ $testimonial->author_name }},

Thanks for taking the time. This is a copy for you. It is not on the site yet — Dirk will read it first, and your email address is never published.

**Training:** {{ $testimonial->trainingType?->name ?? '—' }}

{{ $testimonial->body }}
@else
# New testimonial

{{ $testimonial->author_name }} submitted a testimonial. It stays hidden until you approve it under Testimonials.

- **Name:** {{ $testimonial->author_name }}
- **Email:** {{ $testimonial->author_email }}
- **Training:** {{ $testimonial->trainingType?->name ?? '—' }}

**Testimonial:**

{{ $testimonial->body }}

Reply directly to this email to reach them.
@endif

Thanks,<br>
Tune Up Precision
</x-mail::message>
