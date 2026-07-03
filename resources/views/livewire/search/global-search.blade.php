<div class="relative w-full max-w-md"
     x-data="{ open: false }"
     @keydown.window.ctrl.k.prevent="open = true; $nextTick(() => $refs.searchInput.focus())"
     @keydown.escape.window="open = false"
     @click.outside="open = false">
    <div class="relative">
        <svg class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <input type="search" x-ref="searchInput"
               wire:model.live.debounce.300ms="query"
               @focus="open = true"
               placeholder="Buscar proyectos, actividades, archivos… (Ctrl+K)"
               class="w-full rounded-lg border border-slate-300 bg-white py-2 pl-9 pr-3 text-sm placeholder:text-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100">
    </div>

    <div x-show="open && $wire.query.length >= 2" x-cloak x-transition.opacity
         class="absolute left-0 right-0 z-40 mt-2 max-h-96 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-800">
        @if (! $hasResults)
            <p class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400" wire:loading.remove>Sin resultados para «{{ $query }}».</p>
            <p class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400" wire:loading>Buscando…</p>
        @else
            @if ($projects->isNotEmpty())
                <p class="px-4 pb-1 pt-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Proyectos</p>
                @foreach ($projects as $project)
                    <a href="{{ route('projects.show', $project) }}" wire:navigate @click="open = false"
                       class="flex items-center gap-3 px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700/50">
                        <span class="size-2.5 rounded-full" style="background-color: {{ $project->color }}"></span>
                        <span class="truncate font-medium">{{ $project->name }}</span>
                        <span class="ml-auto truncate text-xs text-slate-400">{{ $project->client_name }}</span>
                    </a>
                @endforeach
            @endif

            @if ($activities->isNotEmpty())
                <p class="px-4 pb-1 pt-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Actividades</p>
                @foreach ($activities as $activity)
                    <a href="{{ route('projects.show', ['project' => $activity->project, 'tab' => 'actividades']) }}" wire:navigate @click="open = false"
                       class="flex items-center gap-3 px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700/50">
                        <x-ui.badge :classes="$activity->status->badgeClasses()">{{ $activity->status->label() }}</x-ui.badge>
                        <span class="truncate">{{ $activity->name }}</span>
                        <span class="ml-auto truncate text-xs text-slate-400">{{ $activity->project->name }}</span>
                    </a>
                @endforeach
            @endif

            @if ($documents->isNotEmpty())
                <p class="px-4 pb-1 pt-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Archivos</p>
                @foreach ($documents as $document)
                    <a href="{{ route('projects.show', ['project' => $document->project, 'tab' => 'archivos']) }}" wire:navigate @click="open = false"
                       class="flex items-center gap-3 px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700/50">
                        <x-ui.badge :classes="$document->category->badgeClasses()">{{ $document->category->label() }}</x-ui.badge>
                        <span class="truncate">{{ $document->name }}</span>
                        <span class="ml-auto truncate text-xs text-slate-400">{{ $document->project->name }}</span>
                    </a>
                @endforeach
            @endif
            <div class="h-2"></div>
        @endif
    </div>
</div>
