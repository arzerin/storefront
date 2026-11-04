@extends('layouts.admin')
@section('title', 'Themes and templates')
@section('heading', 'Themes and templates')
@section('subheading', 'The Ayra theme keeps page layouts and design assets together in the codebase.')
@section('breadcrumb', 'Themes')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><a href="{{ route('admin.site-builder.index') }}" class="btn btn-sm btn-outline-secondary">← Site builder</a><span class="badge text-bg-success">Active theme</span></div>
<div class="card mb-4"><div class="card-body"><h4 class="mb-1">{{ $theme['name'] }}</h4><p class="text-muted mb-2">{{ $theme['description'] }}</p><div class="small text-muted">Theme manifest: <code>resources/themes/ayra/theme.php</code> · Page templates: <code>resources/themes/ayra/views/site-builder/pages/</code> · Styles: <code>resources/css/ayra.css</code></div></div></div>
<h4 class="mb-3">Available page templates</h4><div class="row g-3">@foreach($theme['templates'] as $key => $template)<div class="col-md-4"><div class="card h-100"><div class="card-body"><h5>{{ $template['name'] }}</h5><p class="text-muted mb-0">{{ $template['description'] }}</p></div><div class="card-footer small">Choose this in the page editor.</div></div></div>@endforeach</div>
@endsection
