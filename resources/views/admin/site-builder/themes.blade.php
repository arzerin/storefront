@extends('layouts.admin')
@section('title', 'Storefront themes')
@section('heading', 'Storefront themes')
@section('subheading', 'Install a theme ZIP and switch the design across the entire customer storefront.')
@section('breadcrumb', 'Themes')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><a href="{{ route('admin.site-builder.index') }}" class="btn btn-sm btn-outline-secondary">← Site builder</a><a href="{{ route('home') }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary">View storefront ↗</a></div>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="row g-4 mb-4">
    @foreach($themes as $slug => $theme)
    <div class="col-lg-6"><div class="card h-100 overflow-hidden">
        @if(!empty($theme['thumbnail']))
            <img src="{{ route('theme.asset', ['theme' => $slug, 'asset' => $theme['thumbnail']]) }}" alt="{{ $theme['name'] }} storefront preview" class="admin-theme-preview-image">
        @else
            <div class="admin-theme-preview {{ $slug === 'bold-contemporary' ? 'is-bold' : 'is-minimal' }}" aria-label="{{ $theme['name'] }} visual preview">
                <div class="admin-theme-preview-nav"><strong>AYRA<span>.</span></strong><small>SHOP ALL&nbsp;&nbsp;&nbsp; NEW IN&nbsp;&nbsp;&nbsp; ABOUT</small><span>◯ &nbsp; ♡ &nbsp; ▢</span></div>
                <div class="admin-theme-preview-hero"><div><small>{{ $slug === 'bold-contemporary' ? 'THE NEW PERSPECTIVE / 01' : 'AYRA · THE EVERYDAY EDIT' }}</small><strong>{{ $slug === 'bold-contemporary' ? 'MAKE AN ENTRANCE.' : 'Made for how you move.' }}</strong><span>DISCOVER THE EDIT →</span></div><img src="/media/ayra/hero.webp" alt="" loading="lazy"></div>
                <div class="admin-theme-preview-products"><span>THE COLLECTION</span><span>NEW ARRIVALS</span><span>SHOP BY CATEGORY</span></div>
            </div>
        @endif
        <div class="card-body d-flex flex-column"><div class="d-flex align-items-center gap-2 mb-2"><h4 class="mb-0">{{ $theme['name'] }}</h4>@if($slug === $activeTheme)<span class="badge text-bg-success">Active</span>@endif</div><p class="text-muted mb-2">{{ $theme['description'] }}</p><div class="small text-muted mb-3">By {{ $theme['author'] }} @if(!empty($theme['version'])) · v{{ $theme['version'] }}@endif</div>
            @if($slug !== $activeTheme)<form action="{{ route('admin.site-builder.themes.activate', $slug) }}" method="POST" class="mt-auto">@csrf<button type="submit" class="btn btn-primary">Activate {{ $theme['name'] }}</button></form>@else<div class="mt-auto small text-success">Shown on the homepage, products, cart, checkout, orders and content pages.</div>@endif
        </div>
    </div></div>
    @endforeach
</div>
<div class="card mb-4"><div class="card-body"><h4>Install a theme ZIP</h4><p class="text-muted">Upload a folder containing <code>theme.info.json</code> and <code>theme.css</code>. You can include a thumbnail and an <code>assets/</code> folder with images or WOFF2 fonts. Uploaded themes are installed inactive.</p><form action="{{ route('admin.site-builder.themes.install') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-wrap gap-2 align-items-center">@csrf<input type="file" name="theme_zip" accept=".zip,application/zip" required class="form-control" style="max-width:420px"><button class="btn btn-outline-primary">Install theme</button></form><a href="#theme-guide" class="small d-inline-block mt-3">See package format and CSS examples below ↓</a></div></div>
<div id="theme-guide" class="card mb-4"><div class="card-body"><h4>Theme package format</h4><pre class="admin-theme-code">my-theme/
├── theme.info.json
├── theme.css
├── thumbnail.webp (optional)
└── assets/ (optional images and WOFF2 fonts)</pre><p class="mb-2"><code>theme.info.json</code></p><pre class="admin-theme-code">{
  "slug": "my-theme",
  "name": "My theme",
  "author": "Your studio",
  "description": "A short description of the design.",
  "version": "1.0",
  "thumbnail": "thumbnail.webp"
}</pre><p class="mb-2">CSS is loaded after Ayra's base styles on all customer pages. Example:</p><pre class="admin-theme-code">:root { --ayra-green: #202020; --ayra-cream: #faf7f0; }
.ayra-nav { background: #202020; color: white; }
.ayra-nav .ayra-brand, .ayra-nav-links a { color: white; }
.ayra-hero-copy { background: #202020; color: white; }
.ayra-hero-copy h1 { color: white; }
.ayra-product-card { border-radius: 0; }
.ayra-checkout-summary { background: #faf7f0; }
.ayra-footer { background: #202020; color: white; }</pre><p class="text-muted mb-0">The base design stays available. Activating a theme switches the stylesheet across the storefront; it does not change products, pages or orders. ZIP themes contain styles and images only, so they cannot run PHP.</p></div></div>
<h4 class="mb-3">Page templates</h4><div class="row g-3">@foreach($templates as $key => $template)<div class="col-md-4"><div class="card h-100"><div class="card-body"><h5>{{ $template['name'] }}</h5><p class="text-muted mb-0">{{ $template['description'] }}</p></div><div class="card-footer small">Choose this in the page editor.</div></div></div>@endforeach</div>
@endsection
