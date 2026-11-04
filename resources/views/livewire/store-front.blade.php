<div>
    <section class="hero">
        <div class="hero-copy">
            <span class="eyebrow light">THE EVERYDAY EDIT · 2026</span>
            <h1>Good things.<br><em>Worn often.</em></h1>
            <p>Easy pieces for the days that become your favorites. Made to fit into real life, beautifully.</p>
            <a href="#collection" class="hero-button">Explore the collection <span aria-hidden="true">↗</span></a>
        </div>
        <div class="hero-image" role="img" aria-label="Featured Northline sweater"></div>
        <div class="hero-index">01 / 04 &nbsp;—&nbsp; THE COLLECTION</div>
    </section>

    <section class="intro-strip" aria-label="Store highlights">
        <span>Everyday essentials, considered.</span>
        <span>Thoughtful details <b>✳</b> Easy to wear <b>✳</b> Yours to style</span>
    </section>

    <section id="collection" class="collection-section">
        <div class="section-topline"><span class="eyebrow">CURATED FOR YOU</span><span class="eyebrow">NORTHLINE / 001</span></div>
        <div class="collection-heading">
            <div><h2>Wear it your way<span>.</span></h2><p>Discover the pieces you'll reach for again and again.</p></div>
            <label class="search-field"><span class="sr-only">Search products</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.6"/><path d="m16 16 5 5"/></svg><input wire:model.live.debounce.300ms="searchQuery" type="search" placeholder="Search pieces"></label>
        </div>
        <div class="product-grid">
            @forelse($this->products as $product)
                <a wire:navigate href="{{ route('product.show', $product) }}" class="product-card">
                    <div class="product-media"><img src="{{ $product->featured_image?->path ?? '/media/example4.webp' }}" alt="{{ $product->name }}" loading="lazy"><span class="product-quick">Discover piece <span>↗</span></span></div>
                    <div class="product-meta"><div><span class="product-kicker">NORTHLINE ESSENTIALS</span><h3>{{ $product->name }}</h3></div><span class="product-price">{{ $product->price }}</span></div>
                </a>
            @empty
                <div class="empty-state"><h3>No pieces found.</h3><p>Try another search to explore the collection.</p></div>
            @endforelse
        </div>
        <div class="pagination-wrap">{{ $this->products->links() }}</div>
    </section>
    <section class="closing-note"><span class="eyebrow">THE NORTHLINE POINT OF VIEW</span><p>Less fuss. More feeling.<br><em>Always you.</em></p></section>
</div>
