@php($thumb = $thumb ?? 'size-16')
@if (($comment->screenshots_count ?? $comment->screenshots->count()) > 0)
    @php($count = $comment->screenshots_count ?? $comment->screenshots->count())
    <div class="mt-2">
        <p class="mb-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">
            {{ $count }} {{ $count === 1 ? 'captura' : 'capturas' }}
        </p>
        <div class="flex flex-wrap gap-2">
            @foreach ($comment->screenshots as $screenshot)
                <div class="group relative" wire:key="screenshot-{{ $screenshot->id }}">
                    <a href="{{ $screenshot->imageUrl() }}" target="_blank" rel="noopener noreferrer" title="{{ $screenshot->description }}">
                        <img src="{{ $screenshot->thumbnailUrl() ?? $screenshot->imageUrl() }}" alt="{{ $screenshot->description }}"
                             class="{{ $thumb }} rounded-lg border border-slate-200 object-cover dark:border-slate-700">
                    </a>
                    @can('delete', $screenshot)
                        <button type="button" x-on:click="$store.confirm.open({{ \Illuminate\Support\Js::from('¿Eliminar esta captura?') }}, () => $wire.deleteScreenshot({{ $screenshot->id }}))"
                                class="absolute -right-1.5 -top-1.5 hidden size-5 items-center justify-center rounded-full bg-rose-600 text-white shadow group-hover:flex"
                                aria-label="Eliminar captura">
                            <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                        </button>
                    @endcan
                </div>
            @endforeach
        </div>
    </div>
@endif
