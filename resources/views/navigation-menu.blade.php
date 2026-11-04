<nav class="site-nav" x-data="{ open: false }">
    <div class="nav-inner">
        <a wire:navigate href="{{ route('home') }}" class="brand" aria-label="Northline home">NORTHLINE<span class="brand-dot">.</span></a>
        <div class="nav-links">
            <a wire:navigate href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Shop all</a>
            @auth
                <a wire:navigate href="{{ route('my.orders') }}" class="{{ request()->routeIs('my.orders') ? 'active' : '' }}">My orders</a>
                @can('manage-products')<a wire:navigate href="{{ route('admin.products') }}">Admin</a>@endcan
            @endauth
        </div>
        <div class="nav-actions">
            @guest<a href="{{ route('login') }}" class="account-link">Sign in</a>@endguest
            @auth<a href="{{ route('profile.show') }}" class="account-link">Account</a>@endauth
            @livewire('navigation-cart')
            <button class="menu-toggle" type="button" @click="open = !open" aria-label="Toggle menu" :aria-expanded="open.toString()">
                <span></span><span></span>
            </button>
        </div>
    </div>
    <div class="mobile-menu" x-show="open" x-cloak>
        <a wire:navigate href="{{ route('home') }}">Shop all</a>
        @auth<a wire:navigate href="{{ route('my.orders') }}">My orders</a>@can('manage-products')<a wire:navigate href="{{ route('admin.products') }}">Admin</a>@endcan<a href="{{ route('profile.show') }}">Account</a>@endauth
        @guest<a href="{{ route('login') }}">Sign in</a><a href="{{ route('register') }}">Create account</a>@endguest
    </div>
</nav>
