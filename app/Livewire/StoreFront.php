<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Collection as StoreCollection;
use App\Models\Manufacturer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class StoreFront extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $searchQuery = '';
    #[Url]
    public string $category = '';
    #[Url]
    public string $collection = '';
    #[Url]
    public array $manufacturers = [];
    #[Url]
    public string $color = '';
    #[Url]
    public string $size = '';
    #[Url]
    public string $sort = 'featured';

    public function updated($property): void
    {
        if (in_array($property, ['searchQuery', 'category', 'collection', 'manufacturers', 'color', 'size', 'sort'], true)
            || str_starts_with($property, 'manufacturers.')) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('searchQuery', 'category', 'collection', 'manufacturers', 'color', 'size', 'sort');
        $this->resetPage();
    }

    public function setCategory(string $category): void
    {
        $this->category = $category;
        $this->collection = '';
        $this->resetPage();
    }

    public function setCollection(string $slug): void
    {
        abort_unless(StoreCollection::where('slug', $slug)->where('is_published', true)->exists(), 404);
        $this->collection = $slug;
        $this->category = '';
        $this->resetPage();
    }

    #[Computed]
    public function products(): LengthAwarePaginator
    {
        return Product::query()->where('is_published', true)
            ->when($this->searchQuery, fn ($query) => $query->where(function ($query) {
                $term = '%'.trim($this->searchQuery).'%';
                $query->where('name', 'like', $term)->orWhere('description', 'like', $term)->orWhere('category', 'like', $term);
            }))
            ->when($this->category, fn ($query) => $query->where('category', $this->category))
            ->when($this->collection, fn ($query) => $query->whereHas('collections', fn ($collections) => $collections->where('slug', $this->collection)->where('is_published', true)))
            ->when($this->manufacturers !== [], fn ($query) => $query->whereIn('manufacturer_id', array_filter(array_map('intval', $this->manufacturers))))
            ->when($this->color, fn ($query) => $query->whereHas('variants', fn ($variants) => $variants->where('color', $this->color)))
            ->when($this->size, fn ($query) => $query->whereHas('variants', fn ($variants) => $variants->where('size', $this->size)))
            ->with(['featured_image', 'variants', 'manufacturer'])
            ->when($this->sort === 'newest', fn ($query) => $query->latest())
            ->when(in_array($this->sort, ['price_low', 'price_high'], true), function ($query) {
                $direction = $this->sort === 'price_low' ? 'asc' : 'desc';
                $query->orderByRaw("CASE WHEN sale_price IS NOT NULL AND (sale_starts_at IS NULL OR sale_starts_at <= ?) AND (sale_ends_at IS NULL OR sale_ends_at > ?) THEN sale_price ELSE price END {$direction}", [now(), now()]);
            })
            ->when($this->sort === 'featured', fn ($query) => $query->orderBy('id'))
            ->paginate(8);
    }

    #[Computed]
    public function categories(): Collection
    {
        return Product::where('is_published', true)->whereNotNull('category')->distinct()->orderBy('category')->pluck('category');
    }

    #[Computed]
    public function curatedCollections(): Collection
    {
        return StoreCollection::where('is_published', true)
            ->whereHas('products', fn ($query) => $query->where('is_published', true))
            ->with(['products' => fn ($query) => $query->where('is_published', true)->with('featured_image')])
            ->orderBy('id')->get();
    }

    #[Computed]
    public function manufacturerOptions(): Collection
    {
        return Manufacturer::whereHas('products', fn ($query) => $query->where('is_published', true))
            ->orderBy('name')->get();
    }

    #[Computed]
    public function colors(): Collection
    {
        return Product::where('is_published', true)->join('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->whereNotNull('product_variants.color')->distinct()->orderBy('product_variants.color')->pluck('product_variants.color');
    }

    #[Computed]
    public function sizes(): Collection
    {
        return Product::where('is_published', true)->join('product_variants', 'products.id', '=', 'product_variants.product_id')
            ->whereNotNull('product_variants.size')->distinct()->orderBy('product_variants.size')->pluck('product_variants.size');
    }

    #[Computed]
    public function suggestions(): Collection
    {
        if (mb_strlen(trim($this->searchQuery)) < 2) return collect();
        return Product::where('is_published', true)->where('name', 'like', '%'.trim($this->searchQuery).'%')
            ->with('featured_image')->limit(4)->get();
    }

    #[Computed]
    public function recentlyViewed(): Collection
    {
        $ids = array_slice(session('recently_viewed', []), 0, 3);
        return Product::whereIn('id', $ids)->where('is_published', true)->with('featured_image')->get()
            ->sortBy(fn ($product) => array_search($product->id, $ids, true))->values();
    }

    public function render(): View
    {
        return view('livewire.store-front');
    }
}
