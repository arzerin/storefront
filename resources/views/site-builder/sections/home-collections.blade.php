    <section class="ayra-section ayra-collections">
        <div class="ayra-heading-row"><div><span class="ayra-eyebrow">{{ $section['data']['eyebrow'] ?? '' }}</span><h2>{{ $section['data']['headline'] ?? 'Shop the collections' }}<span>.</span></h2></div><a href="#collection" class="ayra-text-link">Explore all pieces <span>↗</span></a></div>
        <div class="ayra-collection-grid">
            @foreach($this->curatedCollections->filter(fn ($edit) => empty($section['data']['collection_ids']) || in_array($edit->id, $section['data']['collection_ids'])) as $edit)
                <a href="{{ route('collection.show', $edit->slug) }}" class="ayra-collection-card" wire:key="collection-{{ $edit->id }}">
                    <img src="{{ $edit->products->first()?->featured_image?->path ?? '/media/ayra/coat.webp' }}" alt="" loading="lazy"><span class="ayra-collection-shade"></span><span class="ayra-collection-copy"><strong>{{ $edit->name }}</strong><small>{{ $edit->description }}</small><i>Shop now ↗</i></span>
                </a>
            @endforeach
        </div>
    </section>

    <x-site-blocks region="home_after_collections" />
