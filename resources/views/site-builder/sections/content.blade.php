@php($data = $section['data'])
@switch($section['type'])
    @case('hero')
        @php($image = \App\Support\SiteBuilder::image($data))
        <section class="ayra-builder-hero {{ $image ? '' : 'no-image' }}">
            @if($image)<img src="{{ $image['path'] }}" alt="{{ $image['alt'] }}">@endif
            <div><span class="ayra-eyebrow">{{ $data['eyebrow'] ?? '' }}</span><h1>{{ $data['headline'] ?? '' }}</h1><p>{{ $data['body'] ?? '' }}</p>
                @if(!empty($data['button_label']) && !empty($data['button_url']))<a class="ayra-button" href="{{ $data['button_url'] }}">{{ $data['button_label'] }} →</a>@endif
            </div>
        </section>
        @break
    @case('benefits')
        <div class="ayra-benefits">@foreach($data['items'] ?? [] as $item)<span>{{ $item }}</span>@endforeach</div>
        @break
    @case('image_text')
        @php($image = \App\Support\SiteBuilder::image($data))
        <section class="ayra-builder-split"><div class="ayra-builder-split-image">@if($image)<img src="{{ $image['path'] }}" alt="{{ $image['alt'] }}">@endif</div><div class="ayra-builder-split-copy"><span class="ayra-eyebrow">{{ $data['eyebrow'] ?? '' }}</span><h2>{{ $data['headline'] ?? '' }}</h2><p>{!! nl2br(e($data['body'] ?? '')) !!}</p>@if(!empty($data['button_label']) && !empty($data['button_url']))<a class="ayra-text-link" href="{{ $data['button_url'] }}">{{ $data['button_label'] }} ↗</a>@endif</div></section>
        @break
    @case('free_text')
        <section class="ayra-builder-free-text"><div>{!! nl2br(e($data['body'] ?? '')) !!}</div></section>
        @break
    @case('header_text')
        <section class="ayra-builder-header-text"><div><h2>{{ $data['headline'] ?? '' }}</h2><p>{!! nl2br(e($data['body'] ?? '')) !!}</p></div></section>
        @break
    @case('collections')
        @php($ids = $data['collection_ids'] ?? [])
        @php($edits = \App\Models\Collection::where('is_published', true)->whereHas('products', fn ($q) => $q->where('is_published', true))->with(['products' => fn ($q) => $q->where('is_published', true)->with('featured_image')])->when($ids, fn ($q) => $q->whereIn('id', $ids))->limit(6)->get())
        <section class="ayra-section ayra-collections"><div class="ayra-heading-row"><div><span class="ayra-eyebrow">{{ $data['eyebrow'] ?? '' }}</span><h2>{{ $data['headline'] ?? '' }}<span>.</span></h2></div></div><div class="ayra-collection-grid">@foreach($edits as $edit)<a href="{{ route('collection.show', $edit->slug) }}" class="ayra-collection-card"><img src="{{ $edit->products->first()?->featured_image?->path ?? '/media/ayra/coat.webp' }}" alt="" loading="lazy"><span class="ayra-collection-shade"></span><span class="ayra-collection-copy"><strong>{{ $edit->name }}</strong><small>{{ $edit->description }}</small><i>Shop now ↗</i></span></a>@endforeach</div></section>
        @break
    @case('products')
        @php($ids = $data['product_ids'] ?? [])
        @php($products = \App\Models\Product::where('is_published', true)->with(['featured_image', 'variants'])->when($ids, fn ($q) => $q->whereIn('id', $ids))->limit(8)->get())
        <section class="ayra-section ayra-discovery"><div class="ayra-heading-row"><div><span class="ayra-eyebrow">{{ $data['eyebrow'] ?? '' }}</span><h2>{{ $data['headline'] ?? '' }}<span>.</span></h2></div></div><div class="ayra-product-grid">@foreach($products as $product)<a href="{{ route('product.show', $product) }}" class="ayra-product-card"><div class="ayra-product-image"><img src="{{ $product->featured_image?->path ?? '/media/ayra/sweater.webp' }}" alt="{{ $product->name }}" loading="lazy"><span>Explore piece ↗</span></div><div class="ayra-product-meta"><small>{{ strtoupper($product->category ?? 'AYRA EDIT') }}</small><h3>{{ $product->name }}</h3><strong>{{ $product->unitPriceForQuantity(1) }}</strong></div></a>@endforeach</div></section>
        @break
    @case('faq')
        <section class="ayra-builder-faq"><h2>{{ $data['headline'] ?? '' }}</h2>@foreach($data['items'] ?? [] as $item)<details><summary>{{ $item['question'] ?? '' }}</summary><p>{{ $item['answer'] ?? '' }}</p></details>@endforeach</section>
        @break
@endswitch
