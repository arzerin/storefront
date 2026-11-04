<x-app-layout :page-title="$page->title">
    @if($isPreview)<div class="ayra-builder-preview-banner">Draft preview · Only admins can see this page <a href="{{ route('admin.site-builder.edit', $page) }}">Back to editor</a></div>@endif
    @include('site-builder.pages.'.$page->template, ['sections' => $sections])
</x-app-layout>
