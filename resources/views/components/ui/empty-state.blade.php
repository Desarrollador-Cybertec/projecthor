@props(['title' => 'Sin resultados', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 p-10 text-center dark:border-slate-700']) }}>
    <svg class="mb-3 size-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
    </svg>
    <p class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ $title }}</p>
    @if ($description)
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
