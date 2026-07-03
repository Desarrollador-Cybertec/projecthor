<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-semibold">Actividades</h2>
        @can('create', [App\Domains\Activities\Models\Activity::class, $project])
            <x-ui.button size="sm" wire:click="openCreate">Nueva actividad</x-ui.button>
        @endcan
    </div>

    {{-- Filtros --}}
    <div class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-3 dark:border-slate-800 dark:bg-slate-900">
        <x-ui.input placeholder="Buscar actividad…" wire:model.live.debounce.300ms="search" />
        <x-ui.select wire:model.live="statusFilter">
            <option value="">Todos los estados</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.select wire:model.live="stageFilter">
            <option value="">Todas las etapas</option>
            @foreach ($stages as $stage)
                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
            @endforeach
        </x-ui.select>
    </div>

    {{-- Lista --}}
    <div class="space-y-2">
        @forelse ($activities as $activity)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="activity-{{ $activity->id }}">
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex flex-col">
                        <button type="button" wire:click="move({{ $activity->id }}, -1)" class="text-slate-400 hover:text-indigo-500" aria-label="Subir">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 15.75 7.5-7.5 7.5 7.5" /></svg>
                        </button>
                        <button type="button" wire:click="move({{ $activity->id }}, 1)" class="text-slate-400 hover:text-indigo-500" aria-label="Bajar">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                        </button>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" wire:click="openDetail({{ $activity->id }})" class="truncate text-left font-medium hover:text-indigo-600 dark:hover:text-indigo-400">
                                {{ $activity->name }}
                            </button>
                            <x-ui.badge :classes="$activity->priority->badgeClasses()">{{ $activity->priority->label() }}</x-ui.badge>
                            @if ($activity->stage)
                                <span class="text-xs text-slate-400">{{ $activity->stage->name }}</span>
                            @endif
                        </div>
                        <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                            @if ($activity->responsible)
                                <span class="flex items-center gap-1.5"><x-ui.avatar :user="$activity->responsible" size="6" /> {{ $activity->responsible->name }}</span>
                            @endif
                            <span>{{ $activity->evidences_count }} evidencias</span>
                            <span>{{ $activity->comments_count }} observaciones</span>
                            @if ($activity->completed_at)
                                <span>Finalizada {{ $activity->completed_at->diffForHumans() }}</span>
                            @endif
                        </div>
                    </div>

                    @can('changeStatus', $activity)
                        <select wire:change="changeStatus({{ $activity->id }}, $event.target.value)"
                                class="rounded-lg border border-slate-300 bg-white px-2 py-1.5 text-xs font-medium dark:border-slate-600 dark:bg-slate-800">
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected($activity->status->value === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    @else
                        <x-ui.badge :classes="$activity->status->badgeClasses()">{{ $activity->status->label() }}</x-ui.badge>
                    @endcan

                    <div class="flex items-center gap-1">
                        @can('update', $activity)
                            <x-ui.button variant="ghost" size="sm" wire:click="openEdit({{ $activity->id }})">Editar</x-ui.button>
                        @endcan
                        @can('delete', $activity)
                            <x-ui.button variant="ghost" size="sm" wire:click="deleteActivity({{ $activity->id }})" wire:confirm="¿Eliminar la actividad «{{ $activity->name }}»?">
                                <span class="text-rose-600 dark:text-rose-400">Eliminar</span>
                            </x-ui.button>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <x-ui.empty-state title="Sin actividades" description="No se encontraron actividades con los filtros aplicados." />
        @endforelse
    </div>

    {{ $activities->links() }}

    {{-- Modal crear/editar --}}
    <x-ui.modal name="activity-form" :title="$activityId ? 'Editar actividad' : 'Nueva actividad'" max-width="xl">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input label="Nombre" wire:model="name" name="name" required />
            <x-ui.textarea label="Descripción" wire:model="description" name="description" rows="3" />

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.select label="Etapa" wire:model="stage_id" name="stage_id">
                    <option value="">Sin etapa</option>
                    @foreach ($stages as $stage)
                        <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select label="Responsable" wire:model="responsible_id" name="responsible_id">
                    <option value="">Sin asignar</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select label="Prioridad" wire:model="priority" name="priority">
                    @foreach ($priorities as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select label="Estado" wire:model="status" name="status">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button variant="secondary" @click="$dispatch('close-modal', 'activity-form')">Cancelar</x-ui.button>
                <x-ui.button type="submit" wire:loading.attr="disabled">Guardar</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Modal detalle + observaciones --}}
    <x-ui.modal name="activity-detail" :title="$detailActivity?->name ?? 'Actividad'" max-width="2xl">
        @if ($detailActivity)
            <div class="space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.badge :classes="$detailActivity->status->badgeClasses()">{{ $detailActivity->status->label() }}</x-ui.badge>
                    <x-ui.badge :classes="$detailActivity->priority->badgeClasses()">{{ $detailActivity->priority->label() }}</x-ui.badge>
                    @if ($detailActivity->stage)
                        <span class="text-xs text-slate-500">{{ $detailActivity->stage->name }}</span>
                    @endif
                </div>

                @if ($detailActivity->description)
                    <p class="whitespace-pre-line text-sm text-slate-600 dark:text-slate-300">{{ $detailActivity->description }}</p>
                @endif

                <dl class="grid grid-cols-2 gap-3 text-xs text-slate-500 dark:text-slate-400">
                    <div><dt class="font-medium">Creada</dt><dd>{{ $detailActivity->created_at->translatedFormat('d M Y H:i') }}</dd></div>
                    <div><dt class="font-medium">Actualizada</dt><dd>{{ $detailActivity->updated_at->translatedFormat('d M Y H:i') }}</dd></div>
                    @if ($detailActivity->completed_at)
                        <div><dt class="font-medium">Finalizada</dt><dd>{{ $detailActivity->completed_at->translatedFormat('d M Y H:i') }}</dd></div>
                    @endif
                </dl>

                {{-- Evidencias de la actividad --}}
                <div class="border-t border-slate-200 pt-4 dark:border-slate-800" x-data="{ panel: 'none' }">
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <h3 class="text-sm font-semibold">Evidencias ({{ $detailActivity->evidences->count() }})</h3>
                        @can('create', [App\Domains\Evidence\Models\Evidence::class, $project])
                            <div class="flex gap-2">
                                <x-ui.button variant="secondary" size="sm" type="button" @click="panel = panel === 'link' ? 'none' : 'link'">Registrar enlace</x-ui.button>
                                <x-ui.button size="sm" type="button" @click="panel = panel === 'file' ? 'none' : 'file'">Subir archivo</x-ui.button>
                            </div>
                        @endcan
                    </div>

                    <div class="space-y-2">
                        @forelse ($detailActivity->evidences as $evidence)
                            <div wire:key="activity-evidence-{{ $evidence->id }}" class="flex items-center gap-3 rounded-xl border border-slate-200 p-2.5 dark:border-slate-800">
                                @if ($evidence->thumbnailUrl())
                                    <img src="{{ $evidence->thumbnailUrl() }}" alt="" class="size-10 shrink-0 rounded-lg object-cover">
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="truncate text-sm font-medium">{{ $evidence->name }}</span>
                                        <x-ui.badge :classes="$evidence->type->badgeClasses()">{{ $evidence->type->label() }}</x-ui.badge>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">v{{ $evidence->version }} · {{ $evidence->author->name }}</p>
                                </div>
                                @if ($evidence->type->isLinkBased() && $evidence->url)
                                    <a href="{{ $evidence->url }}" target="_blank" rel="noopener noreferrer" class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400">Abrir</a>
                                @elseif ($evidence->file_path)
                                    <a href="{{ route('evidences.download', $evidence) }}" class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400">Descargar</a>
                                @endif
                                @can('delete', $evidence)
                                    <button type="button" wire:click="deleteEvidence({{ $evidence->id }})" wire:confirm="¿Eliminar la evidencia «{{ $evidence->name }}»?"
                                            class="text-xs font-medium text-rose-600 hover:underline dark:text-rose-400">Eliminar</button>
                                @endcan
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 dark:text-slate-400">Sin evidencias en esta actividad.</p>
                        @endforelse
                    </div>

                    @can('create', [App\Domains\Evidence\Models\Evidence::class, $project])
                        {{-- Subir archivo --}}
                        <form x-show="panel === 'file'" x-cloak wire:submit="saveEvidenceFiles" class="mt-3 space-y-3 rounded-xl border border-slate-200 p-3 dark:border-slate-800">
                            <div>
                                <input type="file" wire:model="evidenceFiles" multiple
                                       class="block text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-slate-700 hover:file:bg-slate-200 dark:file:bg-slate-700 dark:file:text-slate-200">
                                @error('evidenceFiles') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                @error('evidenceFiles.*') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                                <div wire:loading wire:target="evidenceFiles" class="mt-1 text-xs text-slate-500">Subiendo…</div>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <x-ui.input label="Versión" wire:model="evidenceVersion" name="evidenceVersion" />
                            </div>
                            <x-ui.textarea label="Descripción" wire:model="evidenceDescription" name="evidenceDescription" rows="2" />
                            <div class="flex justify-end gap-2">
                                <x-ui.button variant="secondary" size="sm" type="button" @click="panel = 'none'">Cancelar</x-ui.button>
                                <x-ui.button size="sm" type="submit" wire:loading.attr="disabled">Subir evidencias</x-ui.button>
                            </div>
                        </form>

                        {{-- Registrar enlace --}}
                        <form x-show="panel === 'link'" x-cloak wire:submit="saveEvidenceLink" class="mt-3 space-y-3 rounded-xl border border-slate-200 p-3 dark:border-slate-800">
                            <x-ui.input label="Nombre" wire:model="evidenceLinkName" name="evidenceLinkName" required />
                            <x-ui.input label="URL" type="url" wire:model="evidenceLinkUrl" name="evidenceLinkUrl" placeholder="https://" required />
                            <div class="grid gap-3 sm:grid-cols-2">
                                <x-ui.select label="Tipo" wire:model="evidenceLinkType" name="evidenceLinkType">
                                    <option value="link">Enlace</option>
                                    <option value="figma">Figma</option>
                                    <option value="production">Producción</option>
                                </x-ui.select>
                                <x-ui.input label="Versión" wire:model="evidenceLinkVersion" name="evidenceLinkVersion" />
                            </div>
                            <x-ui.textarea label="Descripción" wire:model="evidenceLinkDescription" name="evidenceLinkDescription" rows="2" />
                            <div class="flex justify-end gap-2">
                                <x-ui.button variant="secondary" size="sm" type="button" @click="panel = 'none'">Cancelar</x-ui.button>
                                <x-ui.button size="sm" type="submit" wire:loading.attr="disabled">Registrar</x-ui.button>
                            </div>
                        </form>
                    @endcan
                </div>

                {{-- Observaciones de la actividad --}}
                <div class="border-t border-slate-200 pt-4 dark:border-slate-800">
                    <livewire:comments.comment-thread :commentable="$detailActivity" :compact="true" :key="'activity-comments-'.$detailActivity->id" />
                </div>
            </div>
        @endif
    </x-ui.modal>
</div>
