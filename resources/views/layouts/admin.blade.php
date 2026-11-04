<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Products') · Ayra Admin</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary admin-body">
<div class="app-wrapper">
    <nav class="app-header navbar navbar-expand bg-body border-bottom">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Toggle sidebar"><i class="bi bi-list fs-5"></i></a></li>
                <li class="nav-item d-none d-md-block"><span class="nav-link text-muted">Ayra commerce studio</span></li>
            </ul>
            <ul class="navbar-nav ms-auto align-items-center gap-2">
                <li class="nav-item d-none d-sm-block"><a class="nav-link" href="{{ route('home') }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right me-1"></i> View storefront</a></li>
                <li class="nav-item"><span class="nav-link text-muted">{{ auth('admin')->user()->name }}</span></li>
                <li class="nav-item"><form method="post" action="{{ route('admin.logout') }}">@csrf<button type="submit" class="btn btn-sm btn-outline-secondary">Sign out</button></form></li>
            </ul>
        </div>
    </nav>
    <aside class="app-sidebar shadow" data-bs-theme="dark">
        <div class="sidebar-brand"><a href="{{ route('admin.dashboard') }}" class="brand-link"><span class="brand-mark">A</span><span class="brand-text fw-semibold">AYRA Admin</span></a></div>
        <div class="sidebar-wrapper"><nav class="mt-3"><ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">
            <li class="nav-header text-uppercase small text-white-50 px-3 pb-2">Catalogue</li>
            <li class="nav-item"><a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.index') ? 'active' : '' }}"><i class="nav-icon bi bi-grid-3x3-gap"></i><p>Products</p></a></li>
            <li class="nav-item"><a href="{{ route('admin.products.create') }}" class="nav-link {{ request()->routeIs('admin.products.create') ? 'active' : '' }}"><i class="nav-icon bi bi-plus-square"></i><p>Add product</p></a></li>
            <li class="nav-item"><a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"><i class="nav-icon bi bi-tags"></i><p>Categories</p></a></li>
            <li class="nav-item"><a href="{{ route('admin.collections.index') }}" class="nav-link {{ request()->routeIs('admin.collections.*') ? 'active' : '' }}"><i class="nav-icon bi bi-collection"></i><p>Collections</p></a></li>
            <li class="nav-item"><a href="{{ route('admin.manufacturers.index') }}" class="nav-link {{ request()->routeIs('admin.manufacturers.*') ? 'active' : '' }}"><i class="nav-icon bi bi-building"></i><p>Manufacturers</p></a></li>
            <li class="nav-header text-uppercase small text-white-50 px-3 pt-3 pb-2">Storefront</li>
            <li class="nav-item"><a href="{{ route('admin.site-builder.index') }}" class="nav-link {{ request()->routeIs('admin.site-builder.*') ? 'active' : '' }}"><i class="nav-icon bi bi-columns-gap"></i><p>Site builder</p></a></li>
            <li class="nav-item"><a href="{{ route('admin.media-library.index') }}" class="nav-link {{ request()->routeIs('admin.media-library.*') ? 'active' : '' }}"><i class="nav-icon bi bi-images"></i><p>Media library</p></a></li>
            <li class="nav-item"><a href="{{ route('admin.site-settings.edit') }}" class="nav-link {{ request()->routeIs('admin.site-settings.*') ? 'active' : '' }}"><i class="nav-icon bi bi-sliders"></i><p>Site settings</p></a></li>
            <li class="nav-item"><a href="{{ route('admin.blocks.index') }}" class="nav-link {{ request()->routeIs('admin.blocks.*') ? 'active' : '' }}"><i class="nav-icon bi bi-layout-text-window"></i><p>Blocks</p></a></li>
            <li class="nav-item"><a href="{{ route('admin.footer-menu.index') }}" class="nav-link {{ request()->routeIs('admin.footer-menu.*') ? 'active' : '' }}"><i class="nav-icon bi bi-list-nested"></i><p>Footer menu</p></a></li>
        </ul></nav></div>
    </aside>
    <main class="app-main">
        <div class="app-content-header"><div class="container-fluid"><div class="row align-items-center"><div class="col-sm-7"><h3 class="mb-1">@yield('heading', 'Products')</h3><p class="text-muted mb-0">@yield('subheading', 'Manage the Ayra catalogue')</p></div><div class="col-sm-5"><ol class="breadcrumb float-sm-end mb-0"><li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li><li class="breadcrumb-item active">@yield('breadcrumb', 'List')</li></ol></div></div></div></div>
        <div class="app-content"><div class="container-fluid pb-4">@if(session('status'))<div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('status') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>@endif @yield('content')</div></div>
    </main>
    <footer class="app-footer"><span class="text-muted">Ayra Admin</span><span class="float-end">Product catalogue</span></footer>
</div>
</body>
</html>
