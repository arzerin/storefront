@php($benefits = !empty($section['data']['source_blocks']) ? \App\Models\SiteBlock::with('mediaAsset')->where('region', 'home_benefits')->where('is_enabled', true)->orderBy('sort_order')->orderBy('id')->get() : collect())
@if($benefits->isNotEmpty())
    <div class="ayra-benefits">@foreach($benefits as $benefit)<span>@if($benefit->mediaAsset)<img src="{{ $benefit->mediaAsset->path }}" alt="{{ $benefit->mediaAsset->alt_text }}" style="width:22px;height:22px;object-fit:cover;vertical-align:middle">@endif {{ $benefit->title }}</span>@endforeach</div>
@else
    <div class="ayra-benefits">@foreach($section['data']['items'] ?? [] as $benefit)<span>{{ $benefit }}</span>@endforeach</div>
@endif
<x-site-blocks region="home_before_collections" />
