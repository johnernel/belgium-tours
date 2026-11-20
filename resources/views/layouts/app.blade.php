@php
    $pageTitle = trim($__env->yieldContent('title')) ?: $site->brand;
    $pageDesc = trim($__env->yieldContent('description')) ?: $site->meta_description;
    $canonical = trim($__env->yieldContent('canonical')) ?: url()->current();
    $ogImage = trim($__env->yieldContent('og_image')) ?: ($site->social_image ? asset($site->social_image) : asset('images/photos/bruges-canal-1600.webp'));
    $noindex = trim($__env->yieldContent('noindex')) === '1';
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <script>(function(){var d=document.documentElement;d.classList.add('js');function u(){d.classList.toggle('is-scrolled',window.scrollY>8)}u();window.addEventListener('scroll',u,{passive:true})})()</script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDesc }}">
    <link rel="canonical" href="{{ $canonical }}">
    @if($noindex)<meta name="robots" content="noindex, nofollow">@endif
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if($site->recaptcha_key)<meta name="recaptcha-site-key" content="{{ $site->recaptcha_key }}">@endif
    <meta name="theme-color" content="#F7F3EA">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $site->brand }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDesc }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDesc }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    @if($site->favicon)<link rel="icon" href="{{ asset($site->favicon) }}">@else<link rel="icon" href="{{ asset('favicon.ico') }}?v=2" sizes="48x48 32x32"><link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}?v=2"><link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon-192.png') }}?v=2"><link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v=2">@endif

    <link rel="preload" href="/fonts/fraunces-latin-opsz-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/instrument-sans-latin-wght-normal.woff2" as="font" type="font/woff2" crossorigin>
    @stack('preload')

    @if($site->gtm)
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ $site->gtm }}');</script>
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'TravelAgency',
        'name' => $site->brand,
        'legalName' => $site->company,
        'url' => url('/'),
        'description' => $site->meta_description,
        'telephone' => $site->phone ?: null,
        'email' => $site->email ?: null,
        'address' => $site->address ? ['@type' => 'PostalAddress', 'streetAddress' => $site->address] : null,
        'sameAs' => array_values(array_filter([$site->facebook, $site->instagram])) ?: null,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
    @stack('schema')
    {{-- Custom code from Admin > Header & Footer, output exactly as saved --}}
    {!! \App\Support\Snippets::render('head') !!}
</head>
<body class="min-h-screen" x-data>
    {!! \App\Support\Snippets::render('body_start') !!}
    @if($site->gtm)
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $site->gtm }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif

    <a href="#main" class="sr-only z-[100] rounded-full bg-ink px-4 py-2 text-cream focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Skip to content</a>

    {{-- A page may swap the header/footer (the "Homepage Test" design does) with @section('header_view', '...') --}}
    @include(trim($__env->yieldContent('header_view')) ?: 'partials.header')

    <main id="main">
        @yield('content')
    </main>

    @include(trim($__env->yieldContent('footer_view')) ?: 'partials.footer')
    @include('partials.enquiry-modal')

    @stack('scripts')
    {!! \App\Support\Snippets::render('body_end') !!}
</body>
</html>
