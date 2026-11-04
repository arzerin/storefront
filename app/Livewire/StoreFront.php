<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class StoreFront extends Component
{
    use WithPagination;

    #[Url]
    public $searchQuery;

    #[Computed]
    public function products(): LengthAwarePaginator
    {
        return Product::when($this->searchQuery, fn($query) => $query->where('name', 'like', '%' . $this->searchQuery . '%'))
            ->with(['images', 'featured_image'])->paginate(5);
    }

    public function render(): View
    {
        return view('livewire.store-front');
    }
}
