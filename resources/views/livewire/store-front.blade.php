<div class="ayra-home">
    @foreach($sections as $section)
        @switch($section['type'])
            @case('hero') @include('site-builder.sections.home-hero', ['section' => $section]) @break
            @case('benefits') @include('site-builder.sections.home-benefits', ['section' => $section]) @break
            @case('collections') @include('site-builder.sections.home-collections', ['section' => $section]) @break
            @case('products') @include('site-builder.sections.home-products', ['section' => $section]) @break
            @default @include('site-builder.sections.content', ['section' => $section])
        @endswitch
    @endforeach
</div>
