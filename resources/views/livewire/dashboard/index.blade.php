<div class="space-y-8">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Dashboard</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Resumen general de tus proyectos.</p>
        </div>
        <a href="{{ route('projects.export.xlsx') }}"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            Exportar Excel
        </a>
    </div>

    {{-- Indicadores --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.stat-card label="Proyectos activos" :value="$activeProjects">
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" /></svg>
        </x-ui.stat-card>
        <x-ui.stat-card label="Proyectos finalizados" :value="$finishedProjects" icon-classes="bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400">
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
        </x-ui.stat-card>
        <x-ui.stat-card label="Actividades pendientes" :value="$pendingActivities" icon-classes="bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400">
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
        </x-ui.stat-card>
        <x-ui.stat-card label="Actividades finalizadas" :value="$finishedActivities" icon-classes="bg-sky-100 text-sky-600 dark:bg-sky-500/15 dark:text-sky-400">
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" /></svg>
        </x-ui.stat-card>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Próximas entregas --}}
        <x-ui.card title="Próximas entregas">
            @forelse ($upcomingDeliveries as $upcoming)
                <a href="{{ route('projects.show', $upcoming) }}" wire:navigate class="flex items-center justify-between gap-3 border-b border-slate-100 py-2.5 last:border-0 dark:border-slate-800">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-medium">{{ $upcoming->name }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $upcoming->client_name }}</p>
                    </div>
                    <x-ui.badge :classes="$upcoming->due_date->isPast() ? 'bg-rose-100 text-rose-800 dark:bg-rose-500/15 dark:text-rose-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300'">
                        {{ $upcoming->due_date->translatedFormat('d M') }}
                    </x-ui.badge>
                </a>
            @empty
                <p class="py-4 text-sm text-slate-500 dark:text-slate-400">No hay entregas próximas.</p>
            @endforelse
        </x-ui.card>

        {{-- Última actividad --}}
        <x-ui.card title="Última actividad" class="lg:col-span-2">
            @if ($lastEvent)
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-full {{ $lastEvent->type->iconClasses() }}">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $lastEvent->type->icon() }}" /></svg>
                    </span>
                    <div>
                        <p class="text-sm">{{ $lastEvent->description }}</p>
                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ $lastEvent->type->label() }} · {{ $lastEvent->project->name }}
                            @if ($lastEvent->user) · {{ $lastEvent->user->name }} @endif
                            · {{ $lastEvent->created_at->diffForHumans() }}
                        </p>
                    </div>
                </div>
            @else
                <p class="py-4 text-sm text-slate-500 dark:text-slate-400">Aún no hay actividad registrada.</p>
            @endif
        </x-ui.card>
    </div>

    {{-- Tarjetas de proyecto --}}
    <div>
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold">Proyectos recientes</h2>
            <a href="{{ route('projects.index') }}" wire:navigate class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">Ver todos →</a>
        </div>
        @if ($projects->isEmpty())
            <x-ui.empty-state title="Sin proyectos" description="Todavía no tienes proyectos asignados." />
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($projects as $project)
                    <x-project-card :project="$project" />
                @endforeach
            </div>
        @endif
    </div>
</div>
