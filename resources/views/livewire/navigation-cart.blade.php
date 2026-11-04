<a wire:navigate href="{{ route('cart.show') }}" class="cart-link {{ $this->isActive ? 'active' : '' }}" aria-label="Cart with {{ $this->count() }} items">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M3 4h2l2.1 10.2a2 2 0 0 0 2 1.6h9.1a2 2 0 0 0 2-1.6L21.5 8H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
    <span>Bag</span><span class="cart-count">{{ $this->count() }}</span>
</a>
