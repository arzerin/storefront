@extends('layouts.admin')

@php
    $editing = $collection !== null;
    $selected = old('product_ids', $editing ? $collection->products->pluck('id')->all() : []);
@endphp

@section('title', $editing ? 'Edit collection' : 'Add collection')
@section('heading', $editing ? 'Edit collection' : 'Add collection')
@section('subheading', 'Choose pieces from any category for this storefront edit.')
@section('breadcrumb', $editing ? 'Edit collection' : 'Add collection')

@section('content')
<form method="post" action="{{ $editing ? route('admin.collections.update', $collection) : route('admin.collections.store') }}">@csrf @if($editing) @method('PUT') @endif
    <div class="row g-4"><div class="col-lg-8">
        <div class="card mb-4"><div class="card-header"><h3 class="card-title">Collection details</h3></div><div class="card-body">
            <div class="mb-3"><label class="form-label" for="name">Name</label><input id="name" name="name" value="{{ old('name', $collection?->name) }}" maxlength="100" class="form-control @error('name') is-invalid @enderror" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div><label class="form-label" for="description">Description</label><textarea id="description" name="description" rows="3" maxlength="500" class="form-control @error('description') is-invalid @enderror">{{ old('description', $collection?->description) }}</textarea>@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            @if($editing)<p class="form-help mt-2 mb-0">Storefront URL: <a href="{{ route('collection.show', $collection->slug) }}" target="_blank" rel="noopener">{{ route('collection.show', $collection->slug) }}</a></p>@endif
        </div></div>
        <div class="card"><div class="card-header"><h3 class="card-title">Products</h3></div><div class="card-body"><p class="text-muted small">A product can appear in several collections. Its reporting category stays the same.</p><div class="row g-2">
            @foreach($products as $product)<div class="col-md-6"><label class="border rounded p-2 d-flex align-items-center gap-2 w-100"><input class="form-check-input mt-0" type="checkbox" name="product_ids[]" value="{{ $product->id }}" @checked(in_array($product->id, $selected))><span>{{ $product->name }} <small class="d-block text-muted">{{ $product->category }}</small></span></label></div>@endforeach
        </div>@error('product_ids')<p class="text-danger small mt-2">{{ $message }}</p>@enderror @error('product_ids.*')<p class="text-danger small mt-2">{{ $message }}</p>@enderror</div></div>
    </div><div class="col-lg-4"><div class="card position-sticky" style="top:1rem"><div class="card-header"><h3 class="card-title">Publish</h3></div><div class="card-body"><input type="hidden" name="is_published" value="0"><div class="form-check form-switch"><input id="is_published" name="is_published" type="checkbox" value="1" class="form-check-input" @checked(old('is_published', $editing ? $collection->is_published : true))><label for="is_published" class="form-check-label">Show on storefront</label></div></div><div class="card-footer d-grid gap-2"><button class="btn btn-primary" type="submit">Save collection</button><a href="{{ route('admin.collections.index') }}" class="btn btn-outline-secondary">Back to collections</a></div></div></div></div>
</form>
@endsection
