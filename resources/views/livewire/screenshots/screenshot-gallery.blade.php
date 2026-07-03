<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-semibold">Capturas de pantalla</h2>
        <div class="flex items-center gap-2">
            <x-ui.select wire:model.live="groupBy" class="!w-48">
                <option value="none">Sin agrupar</option>
                <option value="stage">Agrupar por etapa</option>
                <option value="activity">Agrupar por actividad</option>
                <option value="version">Agrupar por versión</option>
            </x-ui.select>
            @can('create', [App\Domains\Screenshots\Models\Screenshot::class, $project])
                <x-ui.button size="sm" wire:click="openUpload">Agregar captura</x-ui.button>
            @endcan
        </div>
    </div>

    @if ($grouped->isEmpty())
        <x-ui.empty-state title="Sin capturas" description="Agrega capturas de pantalla para documentar visualmente el proyecto." />
    @endif

    @foreach ($grouped as $groupName => $screenshots)
        <div wire:key="group-{{ md5((string) $groupName) }}">
            @if ($groupBy !== 'none')
                <h3 class="mb-3 mt-6 text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $groupName }} ({{ $screenshots->count() }})</h3>
            @endif
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($screenshots as $screenshot)
                    <div class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="screenshot-{{ $screenshot->id }}">
                        <button type="button" wire:click="openDetail({{ $screenshot->id }})" class="block w-full">
                            <img src="{{ $screenshot->thumbnailUrl() ?? $screenshot->imageUrl() }}" alt="{{ $screenshot->view_name }}"
                                 class="h-40 w-full bg-slate-100 object-cover transition group-hover:scale-105 dark:bg-slate-800">
                        </button>
                        <div class="p-3">
                            <p class="truncate text-sm font-medium">{{ $screenshot->view_name }}</p>
                            <p class="truncate text-xs text-slate-500 dark:text-slate-400">
                                {{ $screenshot->module ?? 'Sin módulo' }}
                                @if ($screenshot->platform) · {{ $screenshot->platform }} @endif
                                @if ($screenshot->resolution) · {{ $screenshot->resolution }} @endif
                            </p>
                            <div class="mt-2 flex items-center justify-between text-xs text-slate-400">
                                <span>{{ $screenshot->taken_at?->translatedFormat('d M Y') }}</span>
                                @if ($screenshot->version)<span>v{{ $screenshot->version }}</span>@endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    {{-- Modal subir captura --}}
    <x-ui.modal name="screenshot-form" title="Agregar captura" max-width="xl">
        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Imagen</label>
                <input type="file" wire:model="image" accept="image/*"
                       class="block w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-slate-700 dark:file:text-slate-200">
                @error('image') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                <div wire:loading wire:target="image" class="mt-1 text-xs text-slate-500">Subiendo…</div>
                @if ($image && $image->isPreviewable())
                    <img src="{{ $image->temporaryUrl() }}" alt="" class="mt-2 max-h-40 rounded-xl object-contain">
                @endif
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.input label="Vista" wire:model="view_name" name="view_name" placeholder="Ej. Login, Dashboard…" required />
                <x-ui.input label="Módulo" wire:model="module" name="module" />
                <x-ui.input label="Resolución" wire:model="resolution" name="resolution" placeholder="1920x1080" />
                <x-ui.select label="Plataforma" wire:model="platform" name="platform">
                    <option value="Web">Web</option>
                    <option value="Móvil">Móvil</option>
                    <option value="Tablet">Tablet</option>
                    <option value="Escritorio">Escritorio</option>
                </x-ui.select>
                <x-ui.input label="Versión" wire:model="version" name="version" />
                <x-ui.input label="Fecha" type="date" wire:model="taken_at" name="taken_at" />
                <x-ui.select label="Etapa" wire:model="stage_id" name="stage_id">
                    <option value="">Sin etapa</option>
                    @foreach ($stages as $stage)
                        <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select label="Actividad" wire:model="activity_id" name="activity_id">
                    <option value="">Sin actividad</option>
                    @foreach ($activities as $activity)
                        <option value="{{ $activity->id }}">{{ $activity->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <x-ui.textarea label="Descripción" wire:model="description" name="description" rows="2" />
            <x-ui.textarea label="Observaciones" wire:model="notes" name="notes" rows="2" />

            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button variant="secondary" @click="$dispatch('close-modal', 'screenshot-form')">Cancelar</x-ui.button>
                <x-ui.button type="submit" wire:loading.attr="disabled">Guardar captura</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Modal detalle --}}
    <x-ui.modal name="screenshot-detail" :title="$detailScreenshot?->view_name ?? 'Captura'" max-width="4xl">
        @if ($detailScreenshot)
            <div class="space-y-4">
                <img src="{{ $detailScreenshot->imageUrl() }}" alt="{{ $detailScreenshot->view_name }}" class="max-h-[28rem] w-full rounded-xl bg-slate-100 object-contain dark:bg-slate-800">

                <dl class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                    <div><dt class="text-xs text-slate-500 dark:text-slate-400">Módulo</dt><dd class="font-medium">{{ $detailScreenshot->module ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500 dark:text-slate-400">Plataforma</dt><dd class="font-medium">{{ $detailScreenshot->platform ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500 dark:text-slate-400">Resolución</dt><dd class="font-medium">{{ $detailScreenshot->resolution ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500 dark:text-slate-400">Versión</dt><dd class="font-medium">{{ $detailScreenshot->version ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500 dark:text-slate-400">Etapa</dt><dd class="font-medium">{{ $detailScreenshot->stage?->name ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500 dark:text-slate-400">Actividad</dt><dd class="font-medium">{{ $detailScreenshot->activity?->name ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500 dark:text-slate-400">Fecha</dt><dd class="font-medium">{{ $detailScreenshot->taken_at?->translatedFormat('d M Y') ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500 dark:text-slate-400">Autor</dt><dd class="font-medium">{{ $detailScreenshot->author->name }}</dd></div>
                </dl>

                @if ($detailScreenshot->description)
                    <p class="text-sm text-slate-600 dark:text-slate-300">{{ $detailScreenshot->description }}</p>
                @endif
                @if ($detailScreenshot->notes)
                    <p class="rounded-xl bg-amber-50 p-3 text-sm text-amber-800 dark:bg-amber-500/10 dark:text-amber-300">{{ $detailScreenshot->notes }}</p>
                @endif

                @can('delete', $detailScreenshot)
                    <div class="flex justify-end">
                        <x-ui.button variant="danger" size="sm" wire:click="deleteScreenshot({{ $detailScreenshot->id }})" wire:confirm="¿Eliminar esta captura?"
                                     @click="$dispatch('close-modal', 'screenshot-detail')">Eliminar captura</x-ui.button>
                    </div>
                @endcan

                <div class="border-t border-slate-200 pt-4 dark:border-slate-800">
                    <livewire:comments.comment-thread :commentable="$detailScreenshot" :compact="true" :key="'screenshot-comments-'.$detailScreenshot->id" />
                </div>
            </div>
        @endif
    </x-ui.modal>
</div>
