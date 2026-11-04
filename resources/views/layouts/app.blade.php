<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Northline — Everyday essentials</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,500;1,400;1,500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="store-body">
<x-banner />
<div class="site-shell">
    @livewire('navigation-menu')
    @if (isset($header))<header class="page-header">{{ $header }}</header>@endif
    <main>{{ $slot }}</main>
    <footer class="site-footer"><div><a href="{{ route('home') }}" class="brand">NORTHLINE<span class="brand-dot">.</span></a><p>Find your everyday.</p></div><div><span>EXPLORE</span><a href="{{ route('home') }}">Shop all</a><a href="{{ route('cart.show') }}">Your bag</a></div><div><span>ACCOUNT</span>@auth<a href="{{ route('my.orders') }}">My orders</a>@else<a href="{{ route('login') }}">Sign in</a>@endauth</div><small>© {{ date('Y') }} Northline. All rights reserved.</small></footer>
</div>
@stack('modals')
@livewireScripts
</body>
</html>
