<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}"><meta name="theme-color" content="#173b2a">
    <title>{{ \App\Models\SiteSetting::getValue('site_name', 'Ayra') }} — {{ \App\Models\SiteSetting::getValue('tagline', 'Made for how you move.') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/ayra.css', 'resources/js/app.js']) @livewireStyles
</head>
<body class="ayra-body"><x-banner /><div class="ayra-shell">
    @livewire('navigation-menu')
    @if(isset($header))<header class="ayra-page-header">{{ $header }}</header>@endif
    <main>{{ $slot }}</main>
    <footer class="ayra-footer">
        <div><a href="{{ route('home') }}" class="ayra-brand">{{ strtoupper(\App\Models\SiteSetting::getValue('site_name', 'Ayra')) }}<span>.</span></a><p>{{ \App\Models\SiteSetting::getValue('footer_description', 'Made for how you move.') }}</p></div>
        @foreach(\App\Models\FooterMenuItem::SECTIONS as $section => $heading)
            @php($items = \App\Models\FooterMenuItem::where('section', $section)->where('is_enabled', true)->orderBy('sort_order')->orderBy('id')->get()->filter(fn ($item) => $item->visibleToCurrentVisitor()))
            @if($items->isNotEmpty())<div><strong>{{ strtoupper($heading) }}</strong>@foreach($items as $item)<a href="{{ $item->url }}" @if(str_starts_with($item->url, 'http')) rel="noopener noreferrer" @endif>{{ $item->label }}</a>@endforeach</div>@endif
        @endforeach
        <x-site-blocks region="footer" />
        <small>© {{ date('Y') }} {{ \App\Models\SiteSetting::getValue('footer_copyright', 'Ayra. All rights reserved.') }}</small>
    </footer>
</div>@stack('modals') @livewireScripts</body>
</html>
