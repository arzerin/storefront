@extends('layouts.admin')

@section('title', 'Price manager')
@section('heading', 'Price manager')
@section('subheading', 'Edit regular prices and schedule sales across the catalogue.')
@section('breadcrumb', 'Prices')

@section('content')
@if($errors->any())
    <div class="alert alert-danger"><strong>Prices were not saved.</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<div class="card mb-3"><div class="card-body"><form method="get" action="{{ route('admin.prices.index') }}" class="row g-2 align-items-end" role="search">
    <div class="col-md-5"><label for="price-search" class="form-label">Search products</label><input id="price-search" name="search" value="{{ $search }}" class="form-control" placeholder="Search by product name"></div>
    <div class="col-md-4"><label for="price-category" class="form-label">Category</label><select id="price-category" name="category" class="form-select"><option value="">All categories</option>@foreach($categories as $option)<option value="{{ $option->name }}" @selected($category === $option->name)>{{ $option->name }}</option>@endforeach</select></div>
    <div class="col-md-3 d-flex gap-2"><button class="btn btn-primary" type="submit">Apply filters</button><a href="{{ route('admin.prices.index') }}" class="btn btn-outline-secondary">Clear</a></div>
</form></div></div>

<form id="price-manager-form" method="post" action="{{ route('admin.prices.update') }}">@csrf
    <input type="hidden" name="reviewed" id="price-reviewed" value="0">
    <input type="hidden" name="search" value="{{ $search }}"><input type="hidden" name="category" value="{{ $category }}"><input type="hidden" name="page" value="{{ $products->currentPage() }}">
    <div class="card"><div class="card-header d-flex flex-wrap gap-2 align-items-center"><strong class="me-auto">{{ $products->total() }} products</strong><span class="small text-muted">Times use {{ config('app.timezone') }}. Sale end is exclusive.</span><span id="price-dirty-count" class="badge text-bg-secondary">No changes</span></div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0 price-manager-table"><thead class="table-light"><tr><th class="ps-3">Product</th><th>Regular price</th><th>Sale price</th><th>Sale starts</th><th>Sale ends</th><th>Quantity rule</th><th>Status</th></tr></thead><tbody>
            @forelse($products as $product)
                @php
                    $id = $product->id;
                    $regular = number_format((int) $product->price->getAmount() / 100, 2, '.', '');
                    $sale = $product->getRawOriginal('sale_price') === null ? '' : number_format($product->getRawOriginal('sale_price') / 100, 2, '.', '');
                    $starts = $product->sale_starts_at?->format('Y-m-d\TH:i') ?? '';
                    $ends = $product->sale_ends_at?->format('Y-m-d\TH:i') ?? '';
                    $status = $product->saleStatus();
                @endphp
                <tr class="price-manager-row" data-product-name="{{ $product->name }}">
                    <td class="ps-3"><div class="d-flex align-items-center gap-2"><img class="product-thumb" src="{{ $product->featured_image?->path ?? '/media/ayra/sweater.webp' }}" alt=""><div><a class="fw-semibold" href="{{ route('admin.products.edit', $product) }}">{{ $product->name }}</a><div class="small text-muted">{{ $product->category ?? 'Uncategorized' }} · #{{ $id }}</div></div></div><input type="hidden" name="prices[{{ $id }}][snapshot]" value="{{ $product->pricingSnapshot() }}"></td>
                    <td><div class="input-group input-group-sm"><span class="input-group-text">$</span><input type="number" name="prices[{{ $id }}][price]" value="{{ old("prices.$id.price", $regular) }}" data-price-field data-field-label="Regular price" data-original="{{ $regular }}" class="form-control" min="0.01" max="999999.99" step="0.01" required aria-label="Regular price for {{ $product->name }}"></div></td>
                    <td><div class="input-group input-group-sm"><span class="input-group-text">$</span><input type="number" name="prices[{{ $id }}][sale_price]" value="{{ old("prices.$id.sale_price", $sale) }}" data-price-field data-field-label="Sale price" data-original="{{ $sale }}" class="form-control" min="0.01" max="999999.99" step="0.01" placeholder="—" aria-label="Sale price for {{ $product->name }}"></div></td>
                    <td><input type="datetime-local" name="prices[{{ $id }}][sale_starts_at]" value="{{ old("prices.$id.sale_starts_at", $starts) }}" data-price-field data-field-label="Sale starts" data-original="{{ $starts }}" class="form-control form-control-sm" aria-label="Sale start for {{ $product->name }}"></td>
                    <td><input type="datetime-local" name="prices[{{ $id }}][sale_ends_at]" value="{{ old("prices.$id.sale_ends_at", $ends) }}" data-price-field data-field-label="Sale ends" data-original="{{ $ends }}" class="form-control form-control-sm" aria-label="Sale end for {{ $product->name }}"></td>
                    <td><select name="prices[{{ $id }}][discount_mode]" data-price-field data-field-label="Quantity rule" data-original="{{ $product->discount_mode }}" class="form-select form-select-sm" aria-label="Quantity rule for {{ $product->name }}"><option value="combine" @selected(old("prices.$id.discount_mode", $product->discount_mode) === 'combine')>Combine</option><option value="sale_only" @selected(old("prices.$id.discount_mode", $product->discount_mode) === 'sale_only')>Sale only</option></select></td>
                    <td><span class="badge {{ match($status) {'active' => 'text-bg-success', 'scheduled' => 'text-bg-info', 'ended' => 'text-bg-secondary', default => 'text-bg-light'} }}">{{ match($status) {'active' => 'Active sale', 'scheduled' => 'Scheduled', 'ended' => 'Ended', default => 'Regular'} }}</span>@if($status === 'active' || $status === 'scheduled')<div class="small text-muted mt-1">{{ $ends ? 'Regular price resumes after end' : 'No end date' }}</div>@endif</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-5 text-muted">No products match these filters.</td></tr>
            @endforelse
        </tbody></table></div>
        <div class="card-footer d-flex flex-wrap align-items-center justify-content-between gap-2"><span class="small text-muted">Sale prices apply automatically at the start and stop at the end. Existing quantity tiers keep their selected rule.</span><button type="submit" class="btn btn-primary" @disabled($products->isEmpty())>Review changes</button></div>
    </div>
    @if($products->hasPages())<div class="mt-3">{{ $products->links('pagination::bootstrap-5') }}</div>@endif
</form>
<dialog id="price-review-dialog" class="price-review-dialog"><div class="price-review-header"><div><span class="text-uppercase small text-muted fw-semibold">Before saving</span><h2 class="h4 mb-0">Review price changes</h2></div><button type="button" id="price-review-close" class="btn-close" aria-label="Close review"></button></div><div id="price-review-list" class="price-review-list"></div><div class="price-review-actions"><button type="button" id="price-review-back" class="btn btn-outline-secondary">Back to editing</button><button type="button" id="price-review-confirm" class="btn btn-primary">Save these changes</button></div></dialog>
<script src="{{ asset('js/price-manager.js') }}?v={{ filemtime(public_path('js/price-manager.js')) }}" defer></script>
@endsection
