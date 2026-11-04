@extends('layouts.admin')
@section('title', 'Site builder')
@section('heading', 'Site builder')
@section('subheading', 'Build the homepage and publish new storefront pages.')
@section('breadcrumb', 'Site builder')
@section('content')
<div class="d-flex justify-content-end gap-2 mb-3"><a href="{{ route('admin.site-builder.themes') }}" class="btn btn-outline-secondary">Themes &amp; templates</a><a href="{{ route('admin.site-builder.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New page</a></div>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th class="ps-4">Page</th><th>Template</th><th>Status</th><th>URL</th><th class="text-end pe-4">Action</th></tr></thead><tbody>
@foreach($pages as $page)<tr><td class="ps-4"><strong>{{ $page->title }}</strong>@if($page->is_home)<span class="badge text-bg-secondary ms-2">Homepage</span>@endif</td><td>{{ \App\Support\SiteBuilder::templates()[$page->template]['name'] ?? $page->template }}</td><td>{{ $page->published_revision_id ? 'Published' : 'Draft' }}</td><td><code>{{ $page->is_home ? '/' : '/'.$page->slug }}</code></td><td class="text-end pe-4"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.site-builder.edit', $page) }}">Edit page</a></td></tr>@endforeach
</tbody></table></div></div>
@endsection
