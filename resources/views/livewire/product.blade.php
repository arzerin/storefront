<div class="ayra-page ayra-product-page">
    <div class="ayra-breadcrumb"><a href="{{ route('home') }}">Home</a><span>›</span><a href="{{ route('home') }}?category={{ urlencode($product->category ?? '') }}#collection">{{ $product->category ?? 'Collection' }}</a><span>›</span><strong>{{ $product->name }}</strong></div>
    <div class="ayra-detail-layout" x-data="{image: @js($this->galleryImages->first()?->path ?? '/media/ayra/sweater.webp'), zoom: false, x: 50, y: 50, guide: false}" x-on:product-color-changed.window="image = $event.detail.path; zoom = false">
        <div class="ayra-gallery">
            <div class="ayra-thumbnails">
                @foreach($this->galleryImages as $image)
                    <button type="button" @click="image = @js($image->path); zoom = false" :class="image === @js($image->path) ? 'active' : ''" aria-label="View image {{ $loop->iteration }} of {{ $product->name }}"><img src="{{ $image->path }}" alt=""></button>
                @endforeach
            </div>
            <div class="ayra-gallery-main" @mousemove="let r=$el.getBoundingClientRect(); x=Math.max(0,Math.min(100,($event.clientX-r.left)/r.width*100)); y=Math.max(0,Math.min(100,($event.clientY-r.top)/r.height*100))" @mouseenter="zoom=true" @mouseleave="zoom=false" @click="zoom=true" role="button" tabindex="0" @keydown.enter="zoom=!zoom" aria-label="Zoom product image">
                <img :src="image" alt="{{ $product->name }}">
                <span class="ayra-zoom-cursor">＋</span>
                <div class="ayra-zoom-preview" x-show="zoom" x-cloak :style="`background-image:url('${image}');background-position:${x}% ${y}%`"><span>DETAIL VIEW · 3×</span></div>
                <span class="ayra-expand-icon" aria-hidden="true">⤢</span>
            </div>
        </div>
        <div class="ayra-detail-info">
            <span class="ayra-product-label">{{ strtoupper($product->category ?? 'AYRA EDIT') }} · AYRA</span>
            @if($product->manufacturer)<div class="ayra-product-label">Made by {{ $product->manufacturer->name }}</div>@endif
            <h1>{{ $product->name }}</h1>
            <div class="ayra-detail-price">{{ $product->unitPriceForQuantity($quantity) }}@if($product->activeSalePriceCents() !== null)<small class="text-muted ms-2" style="text-decoration:line-through">{{ $product->price }}</small>@endif</div>
            @if($product->quantityDiscounts->isNotEmpty())<div class="mb-3"><strong>Buy more, save more</strong><ul class="mt-1 mb-0">@foreach($product->quantityDiscounts as $tier)<li>{{ $tier->min_quantity }}+ pieces: @if($tier->type === 'percent'){{ $tier->value }}% off each @elseif($tier->type === 'amount')${{ $tier->value }} off each @else${{ $tier->value }} per piece @endif</li>@endforeach</ul>@if($product->activeSalePriceCents() !== null && $product->discount_mode === 'sale_only')<small>Quantity tiers resume when this sale ends.</small>@endif</div>@endif
            <p class="ayra-detail-description">{{ $product->description }}</p>
            <div class="ayra-option-block"><div class="ayra-option-heading"><strong>Color: {{ $selectedColor }}</strong></div><div class="ayra-color-options">@foreach($this->colors as $option)<button type="button" wire:click="selectColor('{{ $option }}')" title="{{ $option }}" aria-label="Select {{ $option }}" aria-pressed="{{ $selectedColor === $option ? 'true' : 'false' }}" class="{{ $selectedColor === $option ? 'selected' : '' }}" style="--swatch:{{ \App\Models\ProductVariant::swatchColor($option) }}"></button>@endforeach</div></div>
            <div class="ayra-option-block"><div class="ayra-option-heading"><strong>Size: {{ $selectedSize }}</strong><button type="button" @click="guide=true" class="ayra-inline-link">Size guide ↗</button></div><div class="ayra-size-options">@foreach($this->sizes as $option)<button type="button" wire:click="$set('selectedSize', '{{ $option }}')" class="{{ $selectedSize === $option ? 'selected' : '' }}">{{ $option }}</button>@endforeach</div>@error('selectedSize')<p class="ayra-field-error">{{ $message }}</p>@enderror</div>
            <div class="ayra-availability">● &nbsp; Available in your selection</div>
            <div class="ayra-purchase-row"><div class="ayra-quantity"><button type="button" wire:click="$set('quantity', {{ max(1, $quantity - 1) }})" aria-label="Decrease quantity">−</button><span>{{ $quantity }}</span><button type="button" wire:click="$set('quantity', {{ min(20, $quantity + 1) }})" aria-label="Increase quantity">+</button></div><button type="button" wire:click="addToCart" wire:loading.attr="disabled" class="ayra-button">Add to bag <span>↗</span></button></div>
            @error('quantity')<p class="ayra-field-error">{{ $message }}</p>@enderror
            <a href="{{ route('cart.show') }}" class="ayra-inline-link ayra-view-bag">View your bag →</a>
            <div class="ayra-product-notes"><div><span>◈</span><p><strong>Thoughtful details</strong><small>Designed to be worn often.</small></p></div><div><span>◎</span><p><strong>Flexible payment</strong><small>Card or cash on delivery.</small></p></div><div><span>♧</span><p><strong>Need help?</strong><small>Use the size guide before ordering.</small></p></div></div>
            <div x-show="guide" x-cloak class="ayra-modal-backdrop" @click.self="guide=false" @keydown.escape.window="guide=false"><div class="ayra-modal"><button type="button" class="ayra-modal-close" @click="guide=false" aria-label="Close size guide">×</button><span class="ayra-eyebrow">FIND YOUR FIT</span><h2>Size guide</h2><p>Measurements vary by style. Contact us if you need help choosing a size.</p><div class="ayra-size-guide-row"><strong>XS</strong><span>Extra small</span></div><div class="ayra-size-guide-row"><strong>S</strong><span>Small</span></div><div class="ayra-size-guide-row"><strong>M</strong><span>Medium</span></div><div class="ayra-size-guide-row"><strong>L</strong><span>Large</span></div><div class="ayra-size-guide-row"><strong>XL</strong><span>Extra large</span></div></div></div>
        </div>
    </div>
    @if($this->relatedProducts->isNotEmpty())
        <section class="ayra-related"><div class="ayra-heading-row"><div><span class="ayra-eyebrow">MORE TO EXPLORE</span><h2>Complete the look<span>.</span></h2></div><a href="{{ route('home') }}#collection" class="ayra-text-link">Shop all ↗</a></div><div class="ayra-related-grid">@foreach($this->relatedProducts as $related)<a href="{{ route('product.show', $related) }}" class="ayra-product-card"><div class="ayra-product-image"><img src="{{ $related->featured_image?->path ?? '/media/ayra/sweater.webp' }}" alt="{{ $related->name }}" loading="lazy"></div><div class="ayra-product-meta"><small>{{ strtoupper($related->category ?? 'AYRA') }}</small><h3>{{ $related->name }}</h3><strong>{{ $related->unitPriceForQuantity(1) }}</strong></div></a>@endforeach</div></section>
    @endif
</div>
