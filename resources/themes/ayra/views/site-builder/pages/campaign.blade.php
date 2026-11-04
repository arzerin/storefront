<div class="ayra-builder-page ayra-builder-template-{{ $page->template }}">
    @foreach($sections as $section)
        @include('site-builder.sections.content', ['section' => $section])
    @endforeach
</div>
