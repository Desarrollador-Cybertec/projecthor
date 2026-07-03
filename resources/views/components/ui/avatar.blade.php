@props(['user', 'size' => '10'])

@php
    $sizes = [
        '6' => 'size-6 text-[10px]',
        '8' => 'size-8 text-xs',
        '10' => 'size-10 text-sm',
        '12' => 'size-12 text-base',
    ];
    $sizeClasses = $sizes[$size] ?? $sizes['10'];
@endphp

@if ($user?->avatarUrl())
    <img src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}"
         {{ $attributes->merge(['class' => "{$sizeClasses} rounded-full object-cover"]) }}>
@else
    <span {{ $attributes->merge(['class' => "{$sizeClasses} flex items-center justify-center rounded-full bg-indigo-100 font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300"]) }}
          title="{{ $user?->name }}">
        {{ $user?->initials() ?? '?' }}
    </span>
@endif
