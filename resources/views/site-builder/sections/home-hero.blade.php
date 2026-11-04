@php($hero = $section['data'])
@if(!empty($hero['source_settings']))
    @php($hero['eyebrow'] = \App\Models\SiteSetting::getValue('hero_eyebrow', 'AYRA · THE EVERYDAY EDIT'))
    @php($hero['headline'] = \App\Models\SiteSetting::getValue('hero_headline', 'Made for how you move.'))
    @php($hero['body'] = \App\Models\SiteSetting::getValue('hero_description', 'Considered pieces for workdays, weekends and everything in between.'))
    @php($hero['image_path'] = \App\Models\SiteSetting::getValue('hero_image', '/media/ayra/hero.webp'))
    @php($hero['media_asset_id'] = null)
@endif
@php($image = \App\Support\SiteBuilder::image($hero))
    <section class="ayra-hero">
        <div class="ayra-hero-photo"><img src="{{ $image['path'] ?? '/media/ayra/hero.webp' }}" alt="{{ $image['alt'] ?? 'Ayra collection' }}" fetchpriority="high"></div>
        <div class="ayra-hero-copy">
            <span class="ayra-eyebrow">{{ $hero['eyebrow'] ?? '' }}</span>
            <h1>{{ $hero['headline'] ?? '' }}</h1>
            <p>{{ $hero['body'] ?? '' }}</p>
            @if(!empty($section['data']['button_label']) && !empty($section['data']['button_url']))<a class="ayra-text-link" href="{{ $section['data']['button_url'] }}" style="margin-bottom:16px">{{ $section['data']['button_label'] }} ↗</a>@endif
            <div class="ayra-hero-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.7"/><path d="m16 16 5 5"/></svg>
                <input wire:model.live.debounce.250ms="searchQuery" type="search" placeholder="Search coats, knitwear, accessories..." aria-label="Search products">
                <a href="#collection" class="ayra-search-go" aria-label="See search results">→</a>
                @if($this->suggestions->isNotEmpty())
                    <div class="ayra-suggestions">
                        @foreach($this->suggestions as $suggestion)
                            <a href="{{ route('product.show', $suggestion) }}"><img src="{{ $suggestion->featured_image?->path ?? '/media/ayra/sweater.webp' }}" alt=""><span>{{ $suggestion->name }}</span><strong>{{ $suggestion->unitPriceForQuantity(1) }}</strong></a>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="ayra-quick-search"><span>EXPLORE</span><button wire:click="setCategory('Outerwear')" onclick="document.getElementById('collection').scrollIntoView({behavior:'smooth'})">Outerwear</button><button wire:click="setCategory('Knitwear')" onclick="document.getElementById('collection').scrollIntoView({behavior:'smooth'})">Knitwear</button><button wire:click="setCategory('Accessories')" onclick="document.getElementById('collection').scrollIntoView({behavior:'smooth'})">Accessories</button></div>
        </div>
    </section>
