@props(['classes' => 'bg-slate-100 text-slate-700 dark:bg-slate-500/15 dark:text-slate-300'])

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {$classes}"]) }}>
    {{ $slot }}
</span>
