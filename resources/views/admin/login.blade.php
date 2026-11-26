<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin login | {{ config('gotour.admin_name') }}</title>
    @php $adminFavicon = \App\Models\Setting::get('favicon'); @endphp
    @if($adminFavicon)<link rel="icon" href="{{ asset($adminFavicon) }}">@else<link rel="icon" href="{{ asset('favicon.ico') }}?v=2" sizes="48x48 32x32"><link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}?v=2"><link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon-192.png') }}?v=2"><link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v=2">@endif    @vite(['resources/css/admin.css'])
</head>
<body class="admin-login grid min-h-screen place-items-center p-4">
<main class="w-full max-w-sm rounded-[1.75rem] bg-paper p-8 shadow-lift">
    <h1 class="text-3xl">Admin login</h1>
    <p class="mt-1 text-slate">{{ config('gotour.admin_name') }}</p>
    <form method="POST" action="{{ route('admin.login.attempt') }}" class="mt-6 space-y-4">
        @csrf
        @if($errors->any())<p role="alert" class="rounded-lg bg-red-50 p-3 text-sm font-semibold text-red-900">{{ $errors->first() }}</p>@endif
        <x-admin.input name="email" type="email" label="Email" :required="true" autocomplete="username" autofocus />
        <x-admin.input name="password" type="password" label="Password" :required="true" autocomplete="current-password" />
        <x-button type="submit" class="w-full">Log in</x-button>
    </form>
</main>
</body>
</html>
