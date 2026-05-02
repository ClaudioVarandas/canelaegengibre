<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="canela" class="overflow-x-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Canela e Gengibre')</title>
    <meta name="description" content="@yield('meta_description', __('A slow-food, slow-living journal from Portugal.'))">
    @hasSection('robots')
        <meta name="robots" content="@yield('robots')">
    @endif
    @php
        $canonicalQuery = request()->query('page') ? '?page='.(int) request()->query('page') : '';
    @endphp
    <link rel="canonical" href="{{ url()->current().$canonicalQuery }}">

    @foreach (enabled_locales() as $locale => $meta)
        <link rel="alternate" hreflang="{{ str_replace('_', '-', strtolower($locale)) }}"
              href="{{ url('/'.trim($meta['segment'], '/').'/') }}">
    @endforeach
    @php
        $defaultLocale = (string) config('canela.default_locale');
        $defaultMeta = enabled_locales()[$defaultLocale] ?? null;
    @endphp
    @if ($defaultMeta)
        <link rel="alternate" hreflang="x-default" href="{{ url('/'.trim($defaultMeta['segment'], '/').'/') }}">
    @endif

    @php
        $ogTitleFallback = View::yieldContent('title') ?: 'Canela e Gengibre';
        $ogDescriptionFallback = View::yieldContent('meta_description') ?: __('A slow-food, slow-living journal from Portugal.');
    @endphp
    <meta property="og:site_name" content="Canela e Gengibre">
    <meta property="og:title" content="@yield('og_title', $ogTitleFallback)">
    <meta property="og:description" content="@yield('og_description', $ogDescriptionFallback)">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
        <meta property="og:image:alt" content="@yield('og_image_alt', $ogTitleFallback)">
        <meta name="twitter:image" content="@yield('og_image')">
    @elseif (file_exists(public_path('images/og-default.jpg')))
        <meta property="og:image" content="{{ asset('images/og-default.jpg') }}">
        <meta property="og:image:alt" content="Canela e Gengibre">
        <meta name="twitter:image" content="{{ asset('images/og-default.jpg') }}">
    @endif
    <meta property="og:locale" content="{{ app()->getLocale() }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', $ogTitleFallback)">
    <meta name="twitter:description" content="@yield('og_description', $ogDescriptionFallback)">

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
