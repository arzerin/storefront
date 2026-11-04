@extends('layouts.admin')
@section('title', $block->exists ? 'Edit block' : 'Add block')
@section('heading', $block->exists ? 'Edit block' : 'Add block')
@section('subheading', 'Control where this block appears and whether it is visible.')
@section('breadcrumb', 'Blocks')
@section('content')
<form method="post" action="{{ $block->exists ? route('admin.blocks.update', $block) : route('admin.blocks.store') }}" class="card"><div class="card-body p-4">@csrf @if($block->exists) @method('PUT') @endif
<div class="row g-3"><div class="col-md-8"><label class="form-label">Title</label><input name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $block->title) }}" required>@error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="col-md-4"><label class="form-label">Placement</label><select name="region" class="form-select @error('region') is-invalid @enderror">@foreach(\App\Models\SiteBlock::REGIONS as $value => $label)<option value="{{ $value }}" @selected(old('region', $block->region) === $value)>{{ $label }}</option>@endforeach</select>@error('region')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="col-12"><label class="form-label">Text</label><textarea name="body" rows="5" class="form-control @error('body') is-invalid @enderror">{{ old('body', $block->body) }}</textarea><div class="form-text">Plain text; line breaks are preserved. Benefits strip blocks use the title only.</div>@error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="col-md-4"><label class="form-label">Display order</label><input type="number" min="0" max="9999" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $block->sort_order ?? 0) }}" required>@error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="col-md-8 d-flex align-items-end"><label class="form-check"><input type="checkbox" name="is_enabled" value="1" class="form-check-input" @checked(old('is_enabled', $block->exists ? $block->is_enabled : true))><span class="form-check-label">Visible on storefront</span></label></div></div>
</div><div class="card-footer d-flex justify-content-between"><a href="{{ route('admin.blocks.index') }}" class="btn btn-outline-secondary">Cancel</a><button class="btn btn-primary">Save block</button></div></form>
@endsection
