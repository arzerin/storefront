<x-app-layout>
    <div class="ayra-builder-preview-banner">Draft preview · Only admins can see this page <a href="{{ route('admin.site-builder.edit', $page) }}">Back to editor</a></div>
    @livewire('store-front', ['previewPageId' => $page->id])
</x-app-layout>
