{{--
    Shared SEO + Open Graph + JSON-LD tags for public pages.

    Default copy is the homepage pitch. Every non-home page should pass a
    tighter title/description so search results and link previews aren't all
    identical. Pass jsonLd for page-specific structured data (Course, Event,
    Product, VideoObject, etc.) — the LocalBusiness/Organization schema
    below always renders.
--}}
@props([
    'title' => null,
    'description' => null,
    'robots' => 'index, follow',
    'canonical' => null,
    'image' => null,
    'type' => 'website',
    'jsonLd' => null,
])

@php
    $siteName = 'Tune Up Precision';
    $tagline = 'Long Range Precision Training';
    $fullTitle = $title
        ? $title.' · '.$siteName.' — '.$tagline
        : $siteName.' — '.$tagline;
    $description = $description
        ?: 'Long range rifle training with Dirk Pio in South Africa — foundation, applied long range, PRS shooting and precision reloading. Small squads, private range, your own rifle.';
    $canonical = $canonical ?: url()->current();
    $image = $image ?: asset('images/hero-loop-poster.webp');
    $verification = config('services.google.site_verification');

    $organizationSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => $siteName,
        'description' => 'Long range rifle training, precision reloading and PRS coaching in South Africa.',
        'url' => url('/'),
        'logo' => asset('favicon.svg'),
        'image' => $image,
        'areaServed' => 'South Africa',
        'founder' => [
            '@type' => 'Person',
            'name' => 'Dirk Pio',
            'jobTitle' => 'Long Range Precision Instructor',
        ],
        'sameAs' => array_values(array_filter([
            env('SOCIAL_INSTAGRAM_URL'),
            env('SOCIAL_YOUTUBE_URL'),
            env('SOCIAL_FACEBOOK_URL'),
        ])),
        'address' => [
            '@type' => 'PostalAddress',
            'addressCountry' => 'ZA',
        ],
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'contactType' => 'customer service',
            'url' => route('contact.create'),
            'availableLanguage' => ['en'],
        ],
    ];
@endphp

<title>{{ $fullTitle }}</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">

@if ($verification)
    <meta name="google-site-verification" content="{{ $verification }}">
@endif

<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $fullTitle }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:locale" content="en_ZA">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $fullTitle }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">

<script type="application/ld+json">
{!! json_encode($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>

@isset($jsonLd)
    <script type="application/ld+json">
    {!! is_string($jsonLd) ? $jsonLd : json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
@endisset
