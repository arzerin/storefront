@extends('layouts.admin')
@section('title', 'Site settings')
@section('heading', 'Site settings')
@section('subheading', 'Update the storefront identity, hero and footer copy.')
@section('breadcrumb', 'Site settings')
@section('content')
<form method="post" action="{{ route('admin.site-settings.update') }}" enctype="multipart/form-data" class="card"><div class="card-body p-4">
    @csrf @method('PUT')
    @if($errors->any())<div class="alert alert-danger">Please correct the highlighted fields.</div>@endif
    <h4 class="mb-3">Identity</h4>
    <div class="row g-3">
        @foreach(['site_name' => ['Site name', 'Ayra'], 'tagline' => ['Tagline / browser title', 'Made for how you move.']] as $key => [$label, $default])
            <div class="col-md-6"><label class="form-label" for="{{ $key }}">{{ $label }}</label><input class="form-control @error($key) is-invalid @enderror" id="{{ $key }}" name="{{ $key }}" value="{{ old($key, $settings[$key] ?? $default) }}" required>@error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        @endforeach
    </div>
    <h4 class="mt-4 mb-3">Homepage hero</h4>
    <div class="row g-3">
        @foreach(['hero_eyebrow' => ['Eyebrow', 'AYRA · THE EVERYDAY EDIT'], 'hero_headline' => ['Headline', 'Made for how you move.'], 'hero_description' => ['Description', 'Considered pieces for workdays, weekends and everything in between.']] as $key => [$label, $default])
            <div class="col-md-{{ $key === 'hero_description' ? '12' : '6' }}"><label class="form-label" for="{{ $key }}">{{ $label }}</label><input class="form-control @error($key) is-invalid @enderror" id="{{ $key }}" name="{{ $key }}" value="{{ old($key, $settings[$key] ?? $default) }}" required>@error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        @endforeach
        <div class="col-md-6"><label class="form-label" for="hero_image">Hero image</label><input type="file" accept="image/*" class="form-control @error('hero_image') is-invalid @enderror" id="hero_image" name="hero_image"><div class="form-text">JPEG, PNG or WebP, up to 4 MB. Current image stays until you upload a new one.</div>@error('hero_image')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-md-6"><img src="{{ $settings['hero_image'] ?? '/media/ayra/hero.webp' }}" alt="Current hero" style="height:130px;max-width:100%;object-fit:cover;border-radius:8px"></div>
    </div>
    <h4 class="mt-4 mb-3">Footer</h4>
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="footer_description">Brand description</label><input class="form-control @error('footer_description') is-invalid @enderror" id="footer_description" name="footer_description" value="{{ old('footer_description', $settings['footer_description'] ?? 'Made for how you move.') }}">@error('footer_description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="col-md-6"><label class="form-label" for="footer_copyright">Copyright line</label><input class="form-control @error('footer_copyright') is-invalid @enderror" id="footer_copyright" name="footer_copyright" value="{{ old('footer_copyright', $settings['footer_copyright'] ?? 'Ayra. All rights reserved.') }}"><div class="form-text">The year is added automatically.</div>@error('footer_copyright')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    </div>
</div><div class="card-footer text-end"><button class="btn btn-primary">Save settings</button></div></form>
@endsection
