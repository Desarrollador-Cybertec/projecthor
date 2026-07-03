@props(['name', 'title' => null, 'maxWidth' => 'lg'])

@php
    $widths = ['sm' => 'max-w-sm', 'md' => 'max-w-md', 'lg' => 'max-w-lg', 'xl' => 'max-w-xl', '2xl' => 'max-w-2xl', '4xl' => 'max-w-4xl'];
@endphp

<div
    x-data="{ show: false }"
    x-show="show"
    x-cloak
    x-on:open-modal.window="if ((Array.isArray($event.detail) ? $event.detail[0] : $event.detail) === '{{ $name }}') show = true"
    x-on:close-modal.window="let modalName = Array.isArray($event.detail) ? $event.detail[0] : $event.detail; if (!modalName || modalName === '{{ $name }}') show = false"
    x-on:keydown.escape.window="show = false"
    class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto p-4 sm:items-center"
>
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-slate-900/60" @click="show = false"></div>

    <div x-show="show" x-transition
         class="relative w-full {{ $widths[$maxWidth] ?? 'max-w-lg' }} rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-700 dark:bg-slate-900">
        <div class="mb-4 flex items-start justify-between gap-4">
            @if ($title)
                <h2 class="text-lg font-semibold">{{ $title }}</h2>
            @endif
            <button type="button" class="ml-auto rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800" @click="show = false" aria-label="Cerrar">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </button>
        </div>
        {{ $slot }}
    </div>
</div>
