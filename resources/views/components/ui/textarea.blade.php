@props(['label' => null, 'name' => null, 'rows' => 3])

<div>
    @if ($label)
        <label @if($name) for="{{ $name }}" @endif class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">{{ $label }}</label>
    @endif
    <textarea
        rows="{{ $rows }}"
        @if($name) id="{{ $name }}" name="{{ $name }}" @endif
        {{ $attributes->merge(['class' => 'block w-full rounded-lg border-slate-300 bg-white text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 border px-3 py-2']) }}
    >{{ $slot }}</textarea>
    @if ($name)
        @error($name)
            <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
        @enderror
    @endif
</div>
