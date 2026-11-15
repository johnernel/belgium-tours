<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin') | {{ \App\Models\Setting::get('brand_name', config('app.name')) }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin.js'])
    <style>
        trix-editor { min-height: 12rem; background: #fff; border-radius: .75rem; border: 2px solid rgb(88 97 126 / .3); }
        trix-toolbar .trix-button-group--file-tools { display: none; }
    </style>
</head>
<body class="bg-cream-deep/40 text-ink" x-data="{ nav: false }">
@php
    $links = [
        ['admin.dashboard', 'Dashboard', 'compass'],
        ['admin.enquiries.index', 'Enquiries', 'mail'],
        ['admin.packages.index', 'Packages', 'map-pin'],
        ['admin.tour-types.index', 'Tour types', 'car'],
        ['admin.destinations.index', 'Destinations', 'globe'],
        ['admin.quiz.index', 'Quiz', 'help'],
        ['admin.pages.index', 'Pages', 'calendar'],
        ['admin.testimonials.index', 'Testimonials', 'star'],
        ['admin.faqs.index', 'FAQs', 'help'],
        ['admin.media.index', 'Media', 'plane'],
        ['admin.settings.edit', 'Settings', 'shield'],
        ['admin.users.index', 'Admin users', 'users'],
    ];
@endphp
<div class="flex min-h-screen">
    <aside class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full bg-ink p-4 text-cream transition-transform lg:static lg:translate-x-0" :class="nav && '!translate-x-0'">
        <a href="{{ route('admin.dashboard') }}" class="mb-6 block font-display text-xl font-semibold">{{ \App\Models\Setting::get('brand_name', 'Admin') }}<span class="block text-xs font-normal text-cream/60">Admin</span></a>
        <nav aria-label="Admin" class="space-y-1">
            @foreach($links as [$route, $label, $icon])
                <a href="{{ route($route) }}" class="flex min-h-11 items-center gap-3 rounded-xl px-3 font-semibold hover:bg-white/10 {{ request()->routeIs(Str::beforeLast($route, '.').'*') ? 'bg-white/15' : '' }}">
                    @icon($icon, 'size-5 text-amber') {{ $label }}
                </a>
            @endforeach
        </nav>
        <a href="{{ route('home') }}" target="_blank" class="mt-6 block px-3 text-sm text-cream/70 underline">View site</a>
    </aside>
    <div x-show="nav" x-cloak class="fixed inset-0 z-30 bg-ink/50 lg:hidden" @click="nav = false"></div>

    <div class="min-w-0 flex-1">
        <header class="flex items-center justify-between gap-3 border-b border-ink/10 bg-cream px-4 py-3 sm:px-6">
            <button type="button" class="grid size-11 place-items-center rounded-full hover:bg-ink/5 lg:hidden" @click="nav = true" aria-label="Open menu">@icon('menu')</button>
            <h1 class="font-display text-xl font-semibold sm:text-2xl">@yield('heading')</h1>
            <div class="flex items-center gap-3 text-sm">
                <span class="hidden sm:inline text-slate">{{ auth()->user()->email }}</span>
                <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="min-h-11 rounded-full border-2 border-ink px-4 font-semibold hover:bg-ink hover:text-cream">Log out</button></form>
            </div>
        </header>

        <main class="p-4 sm:p-6">
            @if(session('status'))<div role="status" class="mb-4 rounded-xl bg-sky-soft p-4 font-semibold">{{ session('status') }}</div>@endif
            @if($errors->any())
                <div role="alert" class="mb-4 rounded-xl bg-red-50 p-4 text-red-900">
                    <p class="font-semibold">Please fix the following:</p>
                    <ul class="mt-1 list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
