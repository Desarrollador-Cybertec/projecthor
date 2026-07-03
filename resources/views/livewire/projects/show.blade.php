<div class="space-y-6">
    {{-- Encabezado --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-wrap items-start gap-4">
            @if ($project->logoUrl())
                <img src="{{ $project->logoUrl() }}" alt="" class="size-14 rounded-2xl object-cover">
            @else
                <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl text-xl font-bold text-white" style="background-color: {{ $project->color }}">
                    {{ mb_substr($project->name, 0, 1) }}
                </span>
            @endif

            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl font-semibold tracking-tight">{{ $project->name }}</h1>
                    <x-ui.badge :classes="$project->status->badgeClasses()">{{ $project->status->label() }}</x-ui.badge>
                    <x-ui.badge :classes="$project->priority->badgeClasses()">Prioridad {{ $project->priority->label() }}</x-ui.badge>
                </div>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $project->client_name }}</p>

                <div class="mt-3 flex flex-wrap gap-3 text-sm">
                    @foreach ([
                        ['label' => 'Producción', 'url' => $project->production_url],
                        ['label' => 'Pruebas', 'url' => $project->staging_url],
                        ['label' => 'Documentación', 'url' => $project->documentation_url],
                        ['label' => 'Repositorio', 'url' => $project->repository_url],
                    ] as $link)
                        @if ($link['url'])
                            <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                                {{ $link['label'] }}
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">
                        Exportar ▾
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 z-10 mt-1 w-44 rounded-xl border border-slate-200 bg-white py-1 shadow-lg dark:border-slate-700 dark:bg-slate-800">
                        <a href="{{ route('projects.export.pdf', $project) }}" class="block px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700/50">Reporte PDF</a>
                        <a href="{{ route('projects.activities.export.xlsx', $project) }}" class="block px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-slate-700/50">Actividades Excel</a>
                    </div>
                </div>

                @can('update', $project)
                    <a href="{{ route('projects.edit', $project) }}" wire:navigate
                       class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">
                        Editar
                    </a>
                @endcan

                @can('finish', $project)
                    @unless ($project->isFinished())
                        <x-ui.button wire:click="finish" wire:confirm="¿Marcar este proyecto como finalizado?">Finalizar</x-ui.button>
                    @endunless
                @endcan

                @can('delete', $project)
                    <x-ui.button variant="danger" wire:click="delete" wire:confirm="¿Eliminar este proyecto? Esta acción puede revertirse desde la base de datos.">Eliminar</x-ui.button>
                @endcan
            </div>
        </div>

        {{-- Progreso general --}}
        <div class="mt-6">
            <div class="mb-1 flex items-center justify-between text-sm">
                <span class="font-medium text-slate-700 dark:text-slate-300">Progreso general</span>
                @if ($stage = $project->currentStage())
                    <span class="text-slate-500 dark:text-slate-400">Etapa actual: <span class="font-medium">{{ $stage->name }}</span></span>
                @endif
            </div>
            <x-ui.progress :value="$project->progress" />
        </div>
    </div>

    {{-- Pestañas --}}
    <div class="overflow-x-auto">
        <nav class="flex min-w-max gap-1 rounded-xl border border-slate-200 bg-white p-1 dark:border-slate-800 dark:bg-slate-900">
            @foreach ([
                'resumen' => 'Resumen',
                'etapas' => 'Etapas',
                'actividades' => 'Actividades',
                'archivos' => 'Archivos',
                'observaciones' => 'Observaciones',
                'timeline' => 'Línea de tiempo',
            ] as $key => $label)
                <button type="button" wire:click="setTab('{{ $key }}')"
                        class="rounded-lg px-3.5 py-2 text-sm font-medium transition {{ $tab === $key
                            ? 'bg-indigo-600 text-white'
                            : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- Contenido de la pestaña --}}
    <div>
        @if ($tab === 'resumen')
            <div class="grid gap-6 lg:grid-cols-3">
                <x-ui.card title="Información" class="lg:col-span-2">
                    <p class="whitespace-pre-line text-sm text-slate-600 dark:text-slate-300">{{ $project->description ?: 'Sin descripción.' }}</p>

                    <dl class="mt-6 grid gap-4 text-sm sm:grid-cols-2">
                        <div>
                            <dt class="text-slate-500 dark:text-slate-400">Responsable</dt>
                            <dd class="mt-1 flex items-center gap-2 font-medium"><x-ui.avatar :user="$project->responsible" size="6" /> {{ $project->responsible?->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500 dark:text-slate-400">Equipo</dt>
                            <dd class="mt-1 flex -space-x-1.5">
                                @forelse ($project->members as $member)
                                    <x-ui.avatar :user="$member" size="6" class="ring-2 ring-white dark:ring-slate-900" />
                                @empty
                                    <span class="text-slate-400">Sin miembros asignados</span>
                                @endforelse
                            </dd>
                        </div>
                        <div>
                            <dt class="text-slate-500 dark:text-slate-400">Fecha de inicio</dt>
                            <dd class="mt-1 font-medium">{{ $project->start_date?->translatedFormat('d M Y') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500 dark:text-slate-400">Fecha de entrega</dt>
                            <dd class="mt-1 font-medium">{{ $project->due_date?->translatedFormat('d M Y') ?? '—' }}</dd>
                        </div>
                        @if ($project->finished_at)
                            <div>
                                <dt class="text-slate-500 dark:text-slate-400">Finalizado</dt>
                                <dd class="mt-1 font-medium">{{ $project->finished_at->translatedFormat('d M Y H:i') }}</dd>
                            </div>
                        @endif
                    </dl>
                </x-ui.card>

                <x-ui.card title="Etapas">
                    <ol class="space-y-3">
                        @foreach ($project->stages as $stage)
                            <li>
                                <div class="mb-1 flex items-center justify-between gap-2">
                                    <span class="text-sm font-medium">{{ $stage->name }}</span>
                                    <x-ui.badge :classes="$stage->status->badgeClasses()">{{ $stage->status->label() }}</x-ui.badge>
                                </div>
                                <x-ui.progress :value="$stage->progress" />
                            </li>
                        @endforeach
                    </ol>
                </x-ui.card>
            </div>
        @elseif ($tab === 'etapas')
            <livewire:stages.stage-list :project="$project" :key="'stages-'.$project->id" />
        @elseif ($tab === 'actividades')
            <livewire:activities.activity-list :project="$project" :key="'activities-'.$project->id" />
        @elseif ($tab === 'archivos')
            <livewire:files.document-library :project="$project" :key="'documents-'.$project->id" />
        @elseif ($tab === 'observaciones')
            <livewire:comments.comment-thread :commentable="$project" :key="'comments-'.$project->id" />
        @elseif ($tab === 'timeline')
            <livewire:timeline.project-timeline :project="$project" :key="'timeline-'.$project->id" />
        @endif
    </div>
</div>
