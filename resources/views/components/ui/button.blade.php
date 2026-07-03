@props(['variant' => 'primary', 'size' => 'md', 'type' => 'button'])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-lg font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 disabled:cursor-not-allowed disabled:opacity-50';

    $variants = [
        'primary' => 'bg-indigo-600 text-white hover:bg-indigo-500',
        'secondary' => 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700',
        'danger' => 'bg-rose-600 text-white hover:bg-rose-500',
        'ghost' => 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800',
    ];

    $sizes = [
        'sm' => 'px-2.5 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-2.5 text-base',
    ];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "$base {$variants[$variant]} {$sizes[$size]}"]) }}>
    {{ $slot }}
</button>
