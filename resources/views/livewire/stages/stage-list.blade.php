<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">Etapas del proyecto</h2>
        @can('create', App\Domains\Stages\Models\Stage::class)
            <x-ui.button size="sm" wire:click="openCreate">Agregar etapa</x-ui.button>
        @endcan
    </div>

    <ol class="space-y-3">
        @forelse ($stages as $stage)
            <li class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="stage-{{ $stage->id }}">
                <div class="flex flex-wrap items-start gap-3">
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        {{ $loop->iteration }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-semibold">{{ $stage->name }}</h3>
                            <x-ui.badge :classes="$stage->status->badgeClasses()">{{ $stage->status->label() }}</x-ui.badge>
                            <span class="text-xs text-slate-400">{{ $stage->activities_count }} actividades</span>
                        </div>
                        @if ($stage->description)
                            <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $stage->description }}</p>
                        @endif
                        @if ($stage->objective)
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400"><span class="font-medium">Objetivo:</span> {{ $stage->objective }}</p>
                        @endif
                        <div class="mt-2 flex flex-wrap gap-4 text-xs text-slate-500 dark:text-slate-400">
                            <span>Inicio: {{ $stage->starts_on?->translatedFormat('d M Y') ?? '—' }}</span>
                            <span>Estimada: {{ $stage->estimated_end_on?->translatedFormat('d M Y') ?? '—' }}</span>
                            <span>Final: {{ $stage->ended_on?->translatedFormat('d M Y') ?? '—' }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @can('update', $stage)
                            <x-ui.button variant="secondary" size="sm" wire:click="openEdit({{ $stage->id }})">Editar</x-ui.button>
                        @endcan
                        @can('delete', $stage)
                            <x-ui.button variant="ghost" size="sm" wire:click="deleteStage({{ $stage->id }})" wire:confirm="¿Eliminar la etapa «{{ $stage->name }}»?">
                                <span class="text-rose-600 dark:text-rose-400">Eliminar</span>
                            </x-ui.button>
                        @endcan
                    </div>
                </div>
                <div class="mt-4">
                    <x-ui.progress :value="$stage->progress" />
                </div>
            </li>
        @empty
            <x-ui.empty-state title="Sin etapas" description="Este proyecto no tiene etapas configuradas." />
        @endforelse
    </ol>

    {{-- Modal crear/editar etapa --}}
    <x-ui.modal name="stage-form" :title="$stageId ? 'Editar etapa' : 'Nueva etapa'" max-width="xl">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input label="Nombre" wire:model="name" name="name" required />
            <x-ui.textarea label="Descripción" wire:model="description" name="description" rows="2" />
            <x-ui.textarea label="Objetivo" wire:model="objective" name="objective" rows="2" />

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.select label="Estado" wire:model="status" name="status">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input label="Avance (%)" type="number" min="0" max="100" wire:model="progress" name="progress" />
                <x-ui.input label="Fecha inicio" type="date" wire:model="starts_on" name="starts_on" />
                <x-ui.input label="Fecha estimada" type="date" wire:model="estimated_end_on" name="estimated_end_on" />
                <x-ui.input label="Fecha final" type="date" wire:model="ended_on" name="ended_on" />
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button variant="secondary" @click="$dispatch('close-modal', 'stage-form')">Cancelar</x-ui.button>
                <x-ui.button type="submit" wire:loading.attr="disabled">Guardar</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
