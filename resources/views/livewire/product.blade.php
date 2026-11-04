<div class="detail-wrap">
    <div class="crumbs"><a wire:navigate href="{{ route('home') }}">Shop all</a> / {{ $this->product->name }}</div>
    <div class="detail-grid">
        <div x-data="{ image: @js($this->product->featured_image?->path ?? '/media/example4.webp') }">
            <div class="detail-main-image"><img :src="image" alt="{{ $this->product->name }}"></div>
            <div class="detail-thumbs">
                @foreach($this->product->images as $image)
                    <button type="button" @click="image = @js($image->path)" aria-label="View another image of {{ $this->product->name }}"><img src="{{ $image->path }}" alt=""></button>
                @endforeach
            </div>
        </div>
        <div class="detail-copy">
            <span class="eyebrow">NORTHLINE / EVERYDAY ESSENTIALS</span>
            <h1 class="product-title">{{ $this->product->name }}</h1>
            <div class="detail-price">{{ $this->product->price }}</div>
            <p class="detail-description">{{ $this->product->description }}</p>
            <label class="field-label" for="variant">Choose your size and color</label>
            <select id="variant" wire:model.fill.change="variant" class="variant-select">
                @foreach($this->product->variants as $variant)
                    <option value="{{ $variant->id }}">{{ $variant->size }} / {{ $variant->color }}</option>
                @endforeach
            </select>
            <x-input-error for="variant" class="mt-2" />
            <button wire:click="addToCart" wire:loading.attr="disabled" type="button" class="primary-button">Add to bag <span aria-hidden="true">↗</span></button>
            <p class="product-promise">Easy pieces. Thoughtful details. Made for your everyday.</p>
        </div>
    </div>
</div>
