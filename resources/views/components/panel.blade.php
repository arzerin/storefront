@props(['title' => null])

<div {{ $attributes->class(['bg-white rounded-lg shadow space-y-2 dark:shadow-slate-400 p-4']) }}>
    @if($title)
        <h2 class="font-medium text-lg dark:text-slate-500">{{ $title }}</h2>
    @endif
    {{ $slot }}
</div>
