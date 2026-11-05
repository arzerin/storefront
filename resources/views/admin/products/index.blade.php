@extends('layouts.admin')

@section('title', 'Products')
@section('heading', 'Products')
@section('subheading', 'Review, search and edit every piece in the catalogue.')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3"><div class="small-box text-bg-success"><div class="inner"><h3>{{ \App\Models\Product::count() }}</h3><p>Total products</p></div><i class="small-box-icon bi bi-box-seam"></i></div></div>
    <div class="col-sm-6 col-xl-3"><div class="small-box text-bg-primary"><div class="inner"><h3>{{ \App\Models\Product::where('is_published', true)->count() }}</h3><p>Published products</p></div><i class="small-box-icon bi bi-eye"></i></div></div>
</div>
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center gap-3">
        <h3 class="card-title mb-0 me-auto">Catalogue</h3>
        <form method="get" action="{{ route('admin.products.index') }}" class="d-flex gap-2" role="search"><input name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search name or category" aria-label="Search products"><button class="btn btn-sm btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button></form>
        <a href="{{ route('admin.prices.index') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-currency-dollar me-1"></i>Price manager</a>
        <a href="{{ route('admin.products.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Add product</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th class="ps-4">Product</th><th>Category</th><th>Manufacturer</th><th>Variants</th><th>Price</th><th>Status</th><th class="text-end pe-4">Action</th></tr></thead><tbody>
            @forelse($products as $product)
                <tr><td class="ps-4"><div class="d-flex align-items-center gap-3"><img class="product-thumb" src="{{ $product->featured_image?->path ?? '/media/ayra/sweater.webp' }}" alt=""><div><strong>{{ $product->name }}</strong><div class="text-muted small">#{{ $product->id }}</div></div></div></td><td>{{ $product->category ?? 'Uncategorized' }}</td><td>{{ $product->manufacturer?->name ?? '—' }}</td><td>{{ $product->variants_count }}</td><td class="fw-semibold">{{ $product->price }}</td><td><span class="badge {{ $product->is_published ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $product->is_published ? 'Published' : 'Hidden' }}</span></td><td class="text-end pe-4"><a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square me-1"></i>Edit</a></td></tr>
            @empty
                <tr><td colspan="7" class="text-center py-5 text-muted">No products found. <a href="{{ route('admin.products.create') }}">Add your first product</a>.</td></tr>
            @endforelse
        </tbody></table></div>
    </div>
    @if($products->hasPages())<div class="card-footer">{{ $products->links('pagination::bootstrap-5') }}</div>@endif
</div>
@endsection
