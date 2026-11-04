@extends('layouts.admin')

@php $editing = $manufacturer !== null; @endphp

@section('title', $editing ? 'Edit manufacturer' : 'Add manufacturer')
@section('heading', $editing ? 'Edit manufacturer' : 'Add manufacturer')
@section('subheading', 'Add a maker and assign it from the product editor.')
@section('breadcrumb', $editing ? 'Edit manufacturer' : 'Add manufacturer')

@section('content')
<div class="row"><div class="col-lg-7"><div class="card"><div class="card-header"><h3 class="card-title">Manufacturer details</h3></div>
    <form method="post" action="{{ $editing ? route('admin.manufacturers.update', $manufacturer) : route('admin.manufacturers.store') }}">@csrf @if($editing) @method('PUT') @endif
        <div class="card-body"><div class="mb-3"><label class="form-label" for="name">Name</label><input id="name" name="name" value="{{ old('name', $manufacturer?->name) }}" maxlength="100" class="form-control @error('name') is-invalid @enderror" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div><label class="form-label" for="description">Description (optional)</label><textarea id="description" name="description" rows="4" maxlength="2000" class="form-control @error('description') is-invalid @enderror">{{ old('description', $manufacturer?->description) }}</textarea>@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
        <div class="card-footer d-flex gap-2"><button class="btn btn-primary" type="submit">{{ $editing ? 'Save changes' : 'Create manufacturer' }}</button><a href="{{ route('admin.manufacturers.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
    </form>
</div>@if($editing)<form method="post" action="{{ route('admin.manufacturers.destroy', $manufacturer) }}" class="mt-3" onsubmit="return confirm('Remove this manufacturer? Its products will remain, without a manufacturer.');">@csrf @method('DELETE')<button class="btn btn-outline-danger" type="submit">Remove manufacturer</button></form>@endif</div></div>
@endsection
