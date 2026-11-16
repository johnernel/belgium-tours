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
    <meta name="theme-color" content="#FCF4DF">

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

    @if($site->favicon)<link rel="icon" href="{{ asset($site->favicon) }}">@else<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23FCB345'/%3E%3Cpath d='M9 21l4-10 3 6 3-8 4 12' fill='none' stroke='%232B2E37' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E">@endif

    <link rel="preload" href="/fonts/young-serif-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>
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
</head>
<body class="min-h-screen" x-data>
    @if($site->gtm)
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $site->gtm }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    @endif

    <a href="#main" class="sr-only z-[100] rounded-full bg-ink px-4 py-2 text-cream focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Skip to content</a>

    @include('partials.header')

    <main id="main">
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.enquiry-modal')

    @stack('scripts')
</body>
</html>
