@extends('layouts.admin')

@php
    $editing = $product !== null;
    $variantText = $editing ? $product->variants->map(fn ($variant) => $variant->size.', '.$variant->color)->implode("\n") : '';
    $priceText = $editing ? number_format((int) $product->price->getAmount() / 100, 2, '.', '') : '';
@endphp

@section('title', $editing ? 'Edit product' : 'Add product')
@section('heading', $editing ? 'Edit product' : 'Add product')
@section('subheading', $editing ? 'Update details, options and photos for '.$product->name.'.' : 'Create a new piece for the Ayra storefront.')
@section('breadcrumb', $editing ? 'Edit' : 'Add')

@section('content')
<form method="post" enctype="multipart/form-data" action="{{ $editing ? route('admin.products.update', $product) : route('admin.products.store') }}">
    @csrf
    @if($editing) @method('PUT') @endif
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card mb-4"><div class="card-header"><h3 class="card-title">Product details</h3></div><div class="card-body">
                <div class="mb-3"><label class="form-label" for="name">Name</label><input id="name" name="name" value="{{ old('name', $product?->name) }}" class="form-control @error('name') is-invalid @enderror" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="mb-3"><label class="form-label" for="description">Description</label><textarea id="description" name="description" rows="5" class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $product?->description) }}</textarea>@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="row g-3"><div class="col-md-6"><label class="form-label" for="category">Category</label><input id="category" name="category" value="{{ old('category', $product?->category) }}" class="form-control @error('category') is-invalid @enderror" placeholder="Accessories" required>@error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-6"><label class="form-label" for="price">Price (USD)</label><div class="input-group"><span class="input-group-text">$</span><input id="price" name="price" type="number" min="0.01" step="0.01" value="{{ old('price', $priceText) }}" class="form-control @error('price') is-invalid @enderror" required>@error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div></div>
            </div></div>
            <div class="card mb-4"><div class="card-header"><h3 class="card-title">Variants</h3></div><div class="card-body"><label class="form-label" for="variants">Size and color options</label><textarea id="variants" name="variants" rows="8" class="form-control font-monospace @error('variants') is-invalid @enderror" placeholder="One Size, Olive&#10;One Size, Black" required>{{ old('variants', $variantText) }}</textarea>@error('variants')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="form-help mt-2">Enter one option per line as <strong>Size, Color</strong>. Keep options already used by bags or orders.</div></div></div>
            <div class="card"><div class="card-header"><h3 class="card-title">Product images</h3></div><div class="card-body">
                @if($editing && $product->images->isNotEmpty())
                    <p class="form-label">Current images</p><div class="d-flex flex-wrap gap-3 mb-4">@foreach($product->images as $image)<label class="border rounded p-2 text-center" style="width:138px"><img class="image-preview d-block mx-auto mb-2" src="{{ $image->path }}" alt=""><span class="d-block small fw-semibold">{{ $image->color ?? 'All colors' }}</span>@if($image->featured)<span class="badge text-bg-success">Featured</span>@endif<div class="form-check mt-2 text-start"><input class="form-check-input" type="checkbox" name="remove_image_ids[]" value="{{ $image->id }}" id="remove-image-{{ $image->id }}"><span class="form-check-label small">Remove</span></div></label>@endforeach</div>
                @endif
                <div class="row g-3"><div class="col-md-6"><label class="form-label" for="featured_image">{{ $editing ? 'Replace featured image' : 'Featured image' }}</label><input id="featured_image" name="featured_image" type="file" accept="image/*" class="form-control @error('featured_image') is-invalid @enderror" @if(!$editing) required @endif>@error('featured_image')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-6"><label class="form-label" for="featured_image_color">Featured image color</label><input id="featured_image_color" name="featured_image_color" value="{{ old('featured_image_color') }}" class="form-control @error('featured_image_color') is-invalid @enderror" placeholder="e.g. Olive">@error('featured_image_color')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
                <div class="row g-3 mt-1"><div class="col-md-6"><label class="form-label" for="gallery_images">Add gallery images</label><input id="gallery_images" name="gallery_images[]" type="file" accept="image/*" multiple class="form-control @error('gallery_images') is-invalid @enderror @error('gallery_images.*') is-invalid @enderror">@error('gallery_images')<div class="invalid-feedback">{{ $message }}</div>@enderror @error('gallery_images.*')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-6"><label class="form-label" for="gallery_image_colors">Gallery image colors</label><textarea id="gallery_image_colors" name="gallery_image_colors" rows="3" class="form-control @error('gallery_image_colors') is-invalid @enderror" placeholder="Olive&#10;Black">{{ old('gallery_image_colors') }}</textarea>@error('gallery_image_colors')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
                <p class="form-help mt-2 mb-0">Use the exact variant color name. For multiple gallery files, enter one color per line in upload order. Leave blank for an image shared by all colors.</p>
            </div></div>
        </div>
        <div class="col-lg-4"><div class="card position-sticky" style="top:1rem"><div class="card-header"><h3 class="card-title">Publish</h3></div><div class="card-body"><input type="hidden" name="is_published" value="0"><div class="form-check form-switch"><input id="is_published" name="is_published" type="checkbox" value="1" class="form-check-input" {{ old('is_published', $editing ? (int) $product->is_published : 1) ? 'checked' : '' }}><label for="is_published" class="form-check-label">Visible on storefront</label></div><p class="form-help mt-3 mb-0">Hidden products remain in the catalogue and can be edited later.</p></div><div class="card-footer d-grid gap-2"><button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i>{{ $editing ? 'Save changes' : 'Create product' }}</button><a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Back to products</a></div></div></div>
    </div>
</form>
@endsection
