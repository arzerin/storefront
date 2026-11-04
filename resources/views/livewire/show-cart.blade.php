<div class="cart-wrap">
    <div class="crumbs"><a wire:navigate href="{{ route('home') }}">Shop all</a> / Your bag</div>
    <h1 class="cart-title">Your bag<span class="brand-dot">.</span></h1>
    @if($this->items->isEmpty())
        <div class="cart-empty"><h2>Nothing in here yet.</h2><p>Find a new favorite from the collection.</p><a wire:navigate href="{{ route('home') }}" class="text-link">Continue shopping ↗</a></div>
    @else
        <div class="cart-layout">
            <div class="cart-items">
                @foreach($this->items as $item)
                    <div class="cart-item" wire:key="cart-item-{{ $item->id }}">
                        <a wire:navigate href="{{ route('product.show', $item->product) }}"><img src="{{ $item->product->featured_image?->path ?? '/media/example4.webp' }}" alt="{{ $item->product->name }}"></a>
                        <div><span class="product-kicker">NORTHLINE ESSENTIALS</span><h2>{{ $item->product->name }}</h2><p>{{ $item->variant->size }} / {{ $item->variant->color }}</p><p>{{ $item->product->price }}</p><div class="quantity-control"><button wire:click="decrement({{ $item->id }})" @disabled($item->quantity == 1) aria-label="Decrease quantity">−</button><span>{{ $item->quantity }}</span><button wire:click="increment({{ $item->id }})" aria-label="Increase quantity">+</button></div></div>
                        <div class="cart-item-end"><strong>{{ $item->subtotal }}</strong><button class="remove-button" wire:click="deleteItem({{ $item->id }})">Remove</button></div>
                    </div>
                @endforeach
            </div>
            <aside class="cart-summary"><h2>Order summary</h2><div class="summary-row"><span>Subtotal</span><strong>{{ $this->cart->total }}</strong></div><p>Shipping and taxes are calculated at checkout.</p>
                @guest<p>Please <a class="text-link" href="{{ route('login') }}">sign in</a> or <a class="text-link" href="{{ route('register') }}">create an account</a> to continue.</p>@endguest
                @auth<button wire:click="checkout" wire:loading.attr="disabled" class="primary-button">Continue to checkout ↗</button>@endauth
            </aside>
        </div>
    @endif
</div>
