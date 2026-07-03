@props(['value' => 0, 'color' => null])

@php
    $value = max(0, min(100, (int) $value));
    $color ??= match (true) {
        $value >= 100 => 'bg-emerald-500',
        $value >= 50 => 'bg-indigo-500',
        default => 'bg-sky-500',
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700" role="progressbar" aria-valuenow="{{ $value }}" aria-valuemin="0" aria-valuemax="100">
        <div class="h-full rounded-full {{ $color }} transition-all" style="width: {{ $value }}%"></div>
    </div>
    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $value }}%</span>
</div>
