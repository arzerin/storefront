@props(['region'])
@php($blocks = \App\Models\SiteBlock::with('mediaAsset')->where('region', $region)->where('is_enabled', true)->orderBy('sort_order')->orderBy('id')->get())
@if($blocks->isNotEmpty())
    <div class="ayra-content-blocks {{ $region === 'footer' ? 'ayra-content-blocks-footer' : '' }}">
        @foreach($blocks as $block)
            <section class="ayra-content-block">@if($block->mediaAsset)<img class="ayra-content-block-image" src="{{ $block->mediaAsset->path }}" alt="{{ $block->mediaAsset->alt_text }}" loading="lazy">@endif<h2>{{ $block->title }}</h2>@if($block->body)<p>{!! nl2br(e($block->body)) !!}</p>@endif</section>
        @endforeach
    </div>
@endif
