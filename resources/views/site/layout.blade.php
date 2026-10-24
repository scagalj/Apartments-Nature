@php
    $lang = app()->getLocale();
    $noindex = trim($__env->yieldContent('noindex')) !== '';
    $alternates = $noindex ? [] : \App\Support\Seo::alternates();
    $canonical = \App\Support\Seo::canonical();
    $title = html_entity_decode(trim($__env->yieldContent('title')), ENT_QUOTES);
    $description = html_entity_decode(trim($__env->yieldContent('description')), ENT_QUOTES);
    $ogImage = html_entity_decode(trim($__env->yieldContent('og_image')), ENT_QUOTES) ?: asset('images/site/og-image.jpg');
    $isHome = request()->routeIs('welcome.locale');
    $assetVersion = fn($path) => asset($path) . '?v=' . (@filemtime(public_path($path)) ?: '1');
    $gaId = config('site.google_analytics_id');
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="{{ $noindex ? 'noindex, follow' : 'index, follow, max-image-preview:large, max-snippet:-1' }}">
    <link rel="canonical" href="{{ $canonical }}">
    @foreach($alternates as $locale => $url)
    <link rel="alternate" hreflang="{{ $locale }}" href="{{ $url }}">
    @endforeach
    @if($alternates)
    <link rel="alternate" hreflang="x-default" href="{{ $isHome ? url('/') : $alternates[config('app.fallback_locale', 'en')] ?? reset($alternates) }}">
    @endif

    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ config('site.name') }}">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:locale" content="{{ \App\Support\Seo::regional($lang) }}">
    @foreach(array_keys($alternates) as $locale)
    @if($locale !== $lang)
    <meta property="og:locale:alternate" content="{{ \App\Support\Seo::regional($locale) }}">
    @endif
    @endforeach
    <meta name="twitter:card" content="summary_large_image">

    <meta name="geo.region" content="HR-17">
    <meta name="geo.placename" content="{{ config('site.address.city') }}">
    <meta name="geo.position" content="{{ config('site.geo.lat') }};{{ config('site.geo.lng') }}">
    <meta name="ICBM" content="{{ config('site.geo.lat') }}, {{ config('site.geo.lng') }}">
    <meta name="theme-color" content="#1f5e4f">
    <meta name="format-detection" content="telephone=no">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="{{ asset('images/site/icon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('images/site/apple-touch-icon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="{{ $assetVersion('css/site.css') }}">
    @stack('head')

    <script type="application/ld+json">{!! \App\Support\Seo::jsonLd(array_merge([\App\Support\Seo::website($lang)], $schema ?? [])) !!}</script>

    @if($gaId)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $gaId }}');
    </script>
    @endif
</head>
<body class="@yield('body_class')">
    <a class="skip-link" href="#main">{{ __('site.skip_to_content') }}</a>

    @include('site.partials.header')

    <main id="main">
        @yield('content')
    </main>

    @include('site.partials.footer')

    <script src="{{ $assetVersion('js/site.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
