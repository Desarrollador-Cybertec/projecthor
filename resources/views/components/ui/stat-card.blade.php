@props(['label', 'value', 'iconClasses' => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-400'])

<div {{ $attributes->merge(['class' => 'flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900']) }}>
    <span class="flex size-11 shrink-0 items-center justify-center rounded-xl {{ $iconClasses }}">
        {{ $slot }}
    </span>
    <div class="min-w-0">
        <p class="truncate text-sm text-slate-500 dark:text-slate-400">{{ $label }}</p>
        <p class="text-2xl font-semibold tracking-tight">{{ $value }}</p>
    </div>
</div>
