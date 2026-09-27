<x-mail::message>
# Site questionnaire — new answers

Dirk saved the site questionnaire on **{{ $submittedAt }}**. Only the fields he actually filled in are shown below.

@forelse ($sections as $section)
## {{ $section['heading'] }}

@foreach ($section['rows'] as $row)
- **{{ $row['label'] }}:** {{ $row['value'] }}
@endforeach

@empty
_Dirk saved the page without filling in any fields yet._
@endforelse

<x-mail::button :url="url('/admin')">
Open in the admin
</x-mail::button>

Thanks,<br>
Tune Up Precision
</x-mail::message>
