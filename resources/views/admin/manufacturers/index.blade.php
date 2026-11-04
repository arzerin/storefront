@extends('layouts.admin')

@section('title', 'Manufacturers')
@section('heading', 'Manufacturers')
@section('subheading', 'Manage the makers shown on products and in storefront filters.')
@section('breadcrumb', 'Manufacturers')

@section('content')
<div class="card"><div class="card-header d-flex align-items-center gap-3"><h3 class="card-title mb-0 me-auto">All manufacturers</h3><a href="{{ route('admin.manufacturers.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Add manufacturer</a></div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th class="ps-4">Name</th><th>Products</th><th class="text-end pe-4">Action</th></tr></thead><tbody>
        @forelse($manufacturers as $manufacturer)
            <tr><td class="ps-4"><strong>{{ $manufacturer->name }}</strong>@if($manufacturer->description)<div class="text-muted small">{{ \Illuminate\Support\Str::limit($manufacturer->description, 100) }}</div>@endif</td><td>{{ $manufacturer->products_count }}</td><td class="text-end pe-4"><a href="{{ route('admin.manufacturers.edit', $manufacturer) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square me-1"></i>Edit</a></td></tr>
        @empty
            <tr><td colspan="3" class="text-center py-5 text-muted">No manufacturers yet. Add one to assign it to products.</td></tr>
        @endforelse
    </tbody></table></div>
</div>
@endsection
