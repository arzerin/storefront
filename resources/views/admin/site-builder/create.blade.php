@extends('layouts.admin')
@section('title', 'New page')
@section('heading', 'Create a page')
@section('subheading', 'Choose a starting template; sections can be changed in the editor.')
@section('breadcrumb', 'New page')
@section('content')
<form action="{{ route('admin.site-builder.store') }}" method="post" class="card"><div class="card-body p-4">@csrf
<div class="row g-3"><div class="col-md-6"><label class="form-label" for="title">Page title</label><input id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>@error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="col-md-6"><label class="form-label" for="slug">Page URL</label><div class="input-group"><span class="input-group-text">/</span><input id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="about" required></div>@error('slug')<div class="text-danger small">{{ $message }}</div>@enderror</div>
<div class="col-12"><label class="form-label" for="template">Template</label><select id="template" name="template" class="form-select">@foreach($templates as $key => $template)<option value="{{ $key }}" @selected(old('template') === $key)>{{ $template['name'] }} — {{ $template['description'] }}</option>@endforeach</select></div></div>
</div><div class="card-footer d-flex justify-content-between"><a href="{{ route('admin.site-builder.index') }}" class="btn btn-outline-secondary">Cancel</a><button class="btn btn-primary">Create draft</button></div></form>
@endsection
