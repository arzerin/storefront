@extends('layouts.admin')

@section('title', 'Edit category')
@section('heading', 'Edit category')
@section('subheading', 'Rename this category across its products.')
@section('breadcrumb', 'Edit category')

@section('content')
<div class="row"><div class="col-lg-6"><div class="card"><div class="card-header"><h3 class="card-title">Category details</h3></div>
    <form method="post" action="{{ route('admin.categories.update', $category) }}">@csrf @method('PUT')
        <div class="card-body"><label class="form-label" for="name">Name</label><input id="name" name="name" value="{{ old('name', $category->name) }}" maxlength="100" class="form-control @error('name') is-invalid @enderror" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="card-footer d-flex gap-2"><button class="btn btn-primary" type="submit">Save changes</button><a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary">Cancel</a></div>
    </form>
</div></div></div>
@endsection
