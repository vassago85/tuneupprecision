@props([
    'title' => 'Rifle Builder',
    'description' => 'Build your precision rifle configuration — barrel, action, stock, optic and load — and share the spec with Dirk.',
    'canonical' => null,
    'image' => null,
])
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-seo-meta
        :title="$title"
        :description="$description"
        :canonical="$canonical"
        :image="$image"
    />
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate" type="application/xml" title="Sitemap" href="{{ url('/sitemap.xml') }}">
    <meta name="theme-color" content="#1F2D3A">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Saira+Condensed:wght@500;600;700;800&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    @include('partials.rifle-builder-styles')
    @livewireStyles
</head>
<body class="rb-page">
    {{ $slot }}
    @livewireScripts
</body>
</html>
