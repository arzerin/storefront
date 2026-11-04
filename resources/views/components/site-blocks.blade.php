@props(['region'])
@php($blocks = \App\Models\SiteBlock::where('region', $region)->where('is_enabled', true)->orderBy('sort_order')->orderBy('id')->get())
@if($blocks->isNotEmpty())
    <div class="ayra-content-blocks {{ $region === 'footer' ? 'ayra-content-blocks-footer' : '' }}">
        @foreach($blocks as $block)
            <section class="ayra-content-block"><h2>{{ $block->title }}</h2>@if($block->body)<p>{!! nl2br(e($block->body)) !!}</p>@endif</section>
        @endforeach
    </div>
@endif
