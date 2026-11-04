@extends('layouts.admin')

@section('title', 'Collections')
@section('heading', 'Collections')
@section('subheading', 'Curate storefront edits across product categories.')
@section('breadcrumb', 'Collections')

@section('content')
<div class="card"><div class="card-header d-flex align-items-center gap-3"><h3 class="card-title mb-0 me-auto">Storefront collections</h3><a href="{{ route('admin.collections.create') }}" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Add collection</a></div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th class="ps-4">Collection</th><th>Products</th><th>Status</th><th class="text-end pe-4">Action</th></tr></thead><tbody>
        @forelse($collections as $collection)
            <tr><td class="ps-4"><strong>{{ $collection->name }}</strong><div class="text-muted small">/collections/{{ $collection->slug }}</div></td><td>{{ $collection->products_count }}</td><td><span class="badge {{ $collection->is_published ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $collection->is_published ? 'Published' : 'Hidden' }}</span></td><td class="text-end pe-4"><a href="{{ route('admin.collections.edit', $collection) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square me-1"></i>Edit</a></td></tr>
        @empty
            <tr><td colspan="4" class="text-center py-5 text-muted">No collections yet.</td></tr>
        @endforelse
    </tbody></table></div>
</div>
@endsection
