@extends('layouts.admin')

@section('title', 'Categories')
@section('heading', 'Categories')
@section('subheading', 'Create and manage the categories used by your products.')
@section('breadcrumb', 'Categories')

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card"><div class="card-header"><h3 class="card-title">Add category</h3></div>
            <form method="post" action="{{ route('admin.categories.store') }}">@csrf
                <div class="card-body"><label class="form-label" for="name">Name</label><input id="name" name="name" value="{{ old('name') }}" maxlength="100" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Footwear" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="card-footer"><button class="btn btn-primary" type="submit"><i class="bi bi-plus-lg me-1"></i>Create category</button></div>
            </form>
        </div>
    </div>
    <div class="col-lg-8"><div class="card"><div class="card-header"><h3 class="card-title">All categories</h3></div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th class="ps-4">Name</th><th>Products</th><th class="text-end pe-4">Action</th></tr></thead><tbody>
            @forelse($categories as $category)
                <tr><td class="ps-4 fw-semibold">{{ $category->name }}</td><td>{{ $category->products_count }}</td><td class="text-end pe-4"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.categories.edit', $category) }}"><i class="bi bi-pencil-square me-1"></i>Edit</a></td></tr>
            @empty
                <tr><td colspan="3" class="text-center py-5 text-muted">No categories yet. Create the first one here.</td></tr>
            @endforelse
        </tbody></table></div>
    </div></div>
</div>
@endsection
