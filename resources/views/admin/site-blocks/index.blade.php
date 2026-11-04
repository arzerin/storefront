@extends('layouts.admin')
@section('title', 'Blocks')
@section('heading', 'Blocks')
@section('subheading', 'Place short content blocks on the homepage or footer.')
@section('breadcrumb', 'Blocks')
@section('content')
<div class="card"><div class="card-header d-flex align-items-center"><h3 class="card-title me-auto mb-0">Content blocks</h3><a class="btn btn-sm btn-primary" href="{{ route('admin.blocks.create') }}">Add block</a></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th class="ps-4">Block</th><th>Placement</th><th>Order</th><th>Status</th><th class="text-end pe-4">Actions</th></tr></thead><tbody>
@forelse($blocks as $block)<tr><td class="ps-4"><strong>{{ $block->title }}</strong><div class="small text-muted">{{ \Illuminate\Support\Str::limit($block->body, 90) }}</div></td><td>{{ \App\Models\SiteBlock::REGIONS[$block->region] ?? $block->region }}</td><td>{{ $block->sort_order }}</td><td>{{ $block->is_enabled ? 'Visible' : 'Hidden' }}</td><td class="text-end pe-4"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.blocks.edit', $block) }}">Edit</a><form method="post" action="{{ route('admin.blocks.destroy', $block) }}" class="d-inline" onsubmit="return confirm('Delete this block?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>
@empty<tr><td colspan="5" class="text-center py-5 text-muted">No blocks yet.</td></tr>@endforelse
</tbody></table></div></div>
@endsection
