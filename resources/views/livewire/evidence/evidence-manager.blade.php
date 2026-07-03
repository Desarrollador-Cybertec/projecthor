<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-semibold">Evidencias</h2>
        @can('create', [App\Domains\Evidence\Models\Evidence::class, $project])
            <div class="flex gap-2">
                <x-ui.button variant="secondary" size="sm" wire:click="openLink">Registrar enlace</x-ui.button>
                <x-ui.button size="sm" wire:click="openUpload">Subir archivos</x-ui.button>
            </div>
        @endcan
    </div>

    {{-- Filtros --}}
    <div class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-3 dark:border-slate-800 dark:bg-slate-900">
        <x-ui.input placeholder="Buscar evidencia…" wire:model.live.debounce.300ms="search" />
        <x-ui.select wire:model.live="typeFilter">
            <option value="">Todos los tipos</option>
            @foreach ($types as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.select wire:model.live="activityFilter">
            <option value="">Todas las actividades</option>
            @foreach ($activities as $activity)
                <option value="{{ $activity->id }}">{{ $activity->name }}</option>
            @endforeach
        </x-ui.select>
    </div>

    {{-- Grid --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($evidences as $evidence)
            <div class="flex flex-col rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="evidence-{{ $evidence->id }}">
                @if ($evidence->thumbnailUrl())
                    <button type="button" wire:click="openDetail({{ $evidence->id }})" class="mb-3 block overflow-hidden rounded-xl">
                        <img src="{{ $evidence->thumbnailUrl() }}" alt="{{ $evidence->name }}" class="h-36 w-full object-cover transition hover:scale-105">
                    </button>
                @endif
                <div class="mb-2 flex items-start justify-between gap-2">
                    <button type="button" wire:click="openDetail({{ $evidence->id }})" class="truncate text-left font-medium hover:text-indigo-600 dark:hover:text-indigo-400">
                        {{ $evidence->name }}
                    </button>
                    <x-ui.badge :classes="$evidence->type->badgeClasses()">{{ $evidence->type->label() }}</x-ui.badge>
                </div>
                @if ($evidence->description)
                    <p class="mb-2 line-clamp-2 text-sm text-slate-500 dark:text-slate-400">{{ $evidence->description }}</p>
                @endif
                <div class="mt-auto flex items-center justify-between border-t border-slate-100 pt-3 text-xs text-slate-500 dark:border-slate-800 dark:text-slate-400">
                    <span>v{{ $evidence->version }} · {{ $evidence->author->name }}</span>
                    <span>{{ $evidence->created_at->translatedFormat('d M Y') }}</span>
                </div>
                <div class="mt-3 flex items-center gap-2">
                    @if ($evidence->type->isLinkBased() && $evidence->url)
                        <a href="{{ $evidence->url }}" target="_blank" rel="noopener noreferrer" class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400">Abrir enlace</a>
                    @elseif ($evidence->file_path)
                        <a href="{{ route('evidences.download', $evidence) }}" class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400">Descargar</a>
                    @endif
                    @can('delete', $evidence)
                        <button type="button" wire:click="deleteEvidence({{ $evidence->id }})" wire:confirm="¿Eliminar la evidencia «{{ $evidence->name }}»?"
                                class="ml-auto text-xs font-medium text-rose-600 hover:underline dark:text-rose-400">Eliminar</button>
                    @endcan
                </div>
            </div>
        @empty
            <div class="sm:col-span-2 xl:col-span-3">
                <x-ui.empty-state title="Sin evidencias" description="Sube archivos o registra enlaces como evidencia de las actividades." />
            </div>
        @endforelse
    </div>

    {{ $evidences->links() }}

    {{-- Modal subir archivos --}}
    <x-ui.modal name="evidence-upload" title="Subir evidencias" max-width="xl">
        <form wire:submit="saveFiles" class="space-y-4">
            <div
                x-data="{ dragging: false }"
                @dragover.prevent="dragging = true"
                @dragleave.prevent="dragging = false"
                @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }))"
                :class="dragging ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10' : 'border-slate-300 dark:border-slate-700'"
                class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed p-8 text-center transition"
            >
                <svg class="mb-2 size-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" /></svg>
                <p class="text-sm font-medium">Arrastra y suelta archivos aquí</p>
                <p class="mb-3 text-xs text-slate-500 dark:text-slate-400">o selecciónalos desde tu equipo (máx. 10 archivos, 50 MB c/u)</p>
                <input type="file" x-ref="fileInput" wire:model="files" multiple class="hidden" id="evidence-files">
                <label for="evidence-files" class="cursor-pointer rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500">Seleccionar archivos</label>
                <div wire:loading wire:target="files" class="mt-2 text-xs text-indigo-600 dark:text-indigo-400">Subiendo…</div>
            </div>
            @error('files') <p class="text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
            @error('files.*') <p class="text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror

            @if (count($files) > 0)
                <ul class="space-y-1 text-sm text-slate-600 dark:text-slate-300">
                    @foreach ($files as $file)
                        <li class="flex items-center gap-2">
                            <svg class="size-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            {{ $file->getClientOriginalName() }}
                        </li>
                    @endforeach
                </ul>
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.select label="Actividad" wire:model="upload_activity_id" name="upload_activity_id" required>
                    <option value="">Seleccionar…</option>
                    @foreach ($activities as $activity)
                        <option value="{{ $activity->id }}">{{ $activity->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input label="Versión" wire:model="upload_version" name="upload_version" />
            </div>
            <x-ui.textarea label="Descripción" wire:model="upload_description" name="upload_description" rows="2" />

            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button variant="secondary" @click="$dispatch('close-modal', 'evidence-upload')">Cancelar</x-ui.button>
                <x-ui.button type="submit" wire:loading.attr="disabled">Subir evidencias</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Modal enlace --}}
    <x-ui.modal name="evidence-link" title="Registrar enlace" max-width="lg">
        <form wire:submit="saveLink" class="space-y-4">
            <x-ui.input label="Nombre" wire:model="link_name" name="link_name" required />
            <x-ui.input label="URL" type="url" wire:model="link_url" name="link_url" placeholder="https://" required />
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.select label="Tipo" wire:model="link_type" name="link_type">
                    <option value="link">Enlace</option>
                    <option value="figma">Figma</option>
                    <option value="production">Producción</option>
                </x-ui.select>
                <x-ui.input label="Versión" wire:model="link_version" name="link_version" />
            </div>
            <x-ui.select label="Actividad" wire:model="link_activity_id" name="link_activity_id" required>
                <option value="">Seleccionar…</option>
                @foreach ($activities as $activity)
                    <option value="{{ $activity->id }}">{{ $activity->name }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.textarea label="Descripción" wire:model="link_description" name="link_description" rows="2" />

            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button variant="secondary" @click="$dispatch('close-modal', 'evidence-link')">Cancelar</x-ui.button>
                <x-ui.button type="submit" wire:loading.attr="disabled">Registrar</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Modal detalle + observaciones --}}
    <x-ui.modal name="evidence-detail" :title="$detailEvidence?->name ?? 'Evidencia'" max-width="2xl">
        @if ($detailEvidence)
            <div class="space-y-4">
                @if ($detailEvidence->fileUrl() && $detailEvidence->type === App\Domains\Evidence\Enums\EvidenceType::Image)
                    <img src="{{ $detailEvidence->fileUrl() }}" alt="{{ $detailEvidence->name }}" class="max-h-96 w-full rounded-xl object-contain">
                @endif
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <x-ui.badge :classes="$detailEvidence->type->badgeClasses()">{{ $detailEvidence->type->label() }}</x-ui.badge>
                    <span class="text-slate-500">v{{ $detailEvidence->version }}</span>
                    <span class="text-slate-500">· {{ $detailEvidence->author->name }}</span>
                    <span class="text-slate-500">· {{ $detailEvidence->created_at->translatedFormat('d M Y H:i') }}</span>
                    <span class="text-slate-500">· Actividad: {{ $detailEvidence->activity->name }}</span>
                </div>
                @if ($detailEvidence->description)
                    <p class="text-sm text-slate-600 dark:text-slate-300">{{ $detailEvidence->description }}</p>
                @endif
                <div class="border-t border-slate-200 pt-4 dark:border-slate-800">
                    <livewire:comments.comment-thread :commentable="$detailEvidence" :compact="true" :key="'evidence-comments-'.$detailEvidence->id" />
                </div>
            </div>
        @endif
    </x-ui.modal>
</div>
