<div class="space-y-4">
    <h2 class="text-lg font-semibold">Línea de tiempo</h2>

    @if ($events->isEmpty())
        <x-ui.empty-state title="Sin eventos" description="Los eventos del proyecto se registrarán aquí automáticamente." />
    @else
        <ol class="relative space-y-6 border-l border-slate-200 pl-6 dark:border-slate-800">
            @foreach ($events as $event)
                <li class="relative" wire:key="event-{{ $event->id }}">
                    <span class="absolute -left-[2.05rem] flex size-8 items-center justify-center rounded-full ring-4 ring-slate-100 dark:ring-slate-950 {{ $event->type->iconClasses() }}">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $event->type->icon() }}" /></svg>
                    </span>
                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-semibold">{{ $event->type->label() }}</span>
                            <span class="text-xs text-slate-400">{{ $event->created_at->translatedFormat('d M Y H:i') }} · {{ $event->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $event->description }}</p>
                        @if ($event->user)
                            <p class="mt-1 flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                <x-ui.avatar :user="$event->user" size="6" /> {{ $event->user->name }}
                            </p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>

        {{ $events->links() }}
    @endif
</div>
