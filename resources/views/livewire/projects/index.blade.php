<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Proyectos</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Gestiona el ciclo de vida de tus proyectos.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('projects.export.xlsx') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                Exportar Excel
            </a>
            @can('create', App\Domains\Projects\Models\Project::class)
                <a href="{{ route('projects.create') }}" wire:navigate
                   class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    Nuevo proyecto
                </a>
            @endcan
        </div>
    </div>

    {{-- Filtros --}}
    <div class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-2 lg:grid-cols-4 dark:border-slate-800 dark:bg-slate-900">
        <x-ui.input placeholder="Buscar por nombre, cliente…" wire:model.live.debounce.300ms="search" />
        <x-ui.select wire:model.live="status">
            <option value="">Todos los estados</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.select wire:model.live="priority">
            <option value="">Todas las prioridades</option>
            @foreach ($priorities as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.select wire:model.live="sort">
            <option value="recent">Más recientes</option>
            <option value="name">Nombre (A-Z)</option>
            <option value="due_date">Fecha de entrega</option>
        </x-ui.select>
    </div>

    @if ($projects->isEmpty())
        <x-ui.empty-state title="Sin proyectos" description="No se encontraron proyectos con los filtros aplicados." />
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($projects as $project)
                <x-project-card :project="$project" :key="$project->id" />
            @endforeach
        </div>

        {{ $projects->links() }}
    @endif
</div>
