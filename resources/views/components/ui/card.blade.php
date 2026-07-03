@props(['title' => null])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900']) }}>
    @if ($title)
        <h3 class="mb-4 text-base font-semibold text-slate-900 dark:text-slate-100">{{ $title }}</h3>
    @endif
    {{ $slot }}
</div>
