@extends('layouts.admin')
@section('title', 'Footer menu')
@section('heading', 'Footer menu')
@section('subheading', 'Manage links, columns, audience and display order.')
@section('breadcrumb', 'Footer menu')
@section('content')
<div class="card"><div class="card-header d-flex align-items-center"><h3 class="card-title me-auto mb-0">Footer links</h3><a class="btn btn-sm btn-primary" href="{{ route('admin.footer-menu.create') }}">Add link</a></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th class="ps-4">Label and destination</th><th>Column</th><th>Audience</th><th>Order</th><th>Status</th><th class="text-end pe-4">Actions</th></tr></thead><tbody>
@forelse($items as $item)<tr><td class="ps-4"><strong>{{ $item->label }}</strong><div class="small text-muted">{{ $item->url }}</div></td><td>{{ \App\Models\FooterMenuItem::SECTIONS[$item->section] ?? $item->section }}</td><td>{{ \App\Models\FooterMenuItem::AUDIENCES[$item->audience] ?? $item->audience }}</td><td>{{ $item->sort_order }}</td><td>{{ $item->is_enabled ? 'Visible' : 'Hidden' }}</td><td class="text-end pe-4"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.footer-menu.edit', $item) }}">Edit</a><form method="post" action="{{ route('admin.footer-menu.destroy', $item) }}" class="d-inline" onsubmit="return confirm('Delete this link?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>
@empty<tr><td colspan="6" class="text-center py-5 text-muted">No footer links yet.</td></tr>@endforelse
</tbody></table></div></div>
@endsection
