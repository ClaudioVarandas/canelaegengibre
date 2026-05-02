<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="canela" class="overflow-x-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Canela e Gengibre')</title>
    <meta name="description" content="@yield('meta_description', __('A slow-food, slow-living journal from Portugal.'))">
    <link rel="canonical" href="{{ url()->current() }}">

    @foreach (enabled_locales() as $locale => $meta)
        <link rel="alternate" hreflang="{{ str_replace('_', '-', strtolower($locale)) }}"
              href="{{ url('/'.trim($meta['segment'], '/').'/') }}">
    @endforeach

    <meta property="og:title" content="@yield('og_title', View::yieldContent('title') ?: 'Canela e Gengibre')">
    <meta property="og:description" content="@yield('og_description', View::yieldContent('meta_description') ?: __('A slow-food, slow-living journal from Portugal.'))">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
    @endif
    <meta property="og:locale" content="{{ app()->getLocale() }}">
    <meta name="twitter:card" content="summary_large_image">

    @yield('structured_data')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fraunces:opsz,wght@9..144,400..700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen min-w-0 flex-col overflow-x-hidden bg-gradient-to-b from-oat to-[#F3E8D6] font-sans text-ink antialiased">
    @yield('content')
</body>
</html>
