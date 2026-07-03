<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-semibold">Biblioteca documental</h2>
        @can('create', [App\Domains\Files\Models\Document::class, $project])
            <x-ui.button size="sm" wire:click="openUpload">Cargar archivo</x-ui.button>
        @endcan
    </div>

    {{-- Filtros --}}
    <div class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-2 dark:border-slate-800 dark:bg-slate-900">
        <x-ui.input placeholder="Buscar archivo…" wire:model.live.debounce.300ms="search" />
        <x-ui.select wire:model.live="categoryFilter">
            <option value="">Todas las categorías</option>
            @foreach ($categories as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </x-ui.select>
    </div>

    {{-- Lista --}}
    <div class="space-y-2">
        @forelse ($documents as $document)
            <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900" wire:key="document-{{ $document->id }}">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <p class="truncate font-medium">{{ $document->name }}</p>
                        <x-ui.badge :classes="$document->category->badgeClasses()">{{ $document->category->label() }}</x-ui.badge>
                        <span class="text-xs text-slate-400">v{{ $document->latestVersion?->version ?? 1 }} · {{ $document->versions_count }} versiones</span>
                    </div>
                    @if ($document->description)
                        <p class="truncate text-sm text-slate-500 dark:text-slate-400">{{ $document->description }}</p>
                    @endif
                    <p class="text-xs text-slate-400">
                        {{ $document->uploader->name }} · {{ $document->created_at->translatedFormat('d M Y') }}
                        @if ($document->latestVersion) · {{ Illuminate\Support\Number::fileSize($document->latestVersion->file_size) }} @endif
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-1">
                    @if ($document->latestVersion?->isPreviewable())
                        <x-ui.button variant="ghost" size="sm" wire:click="openPreview({{ $document->id }})">Vista previa</x-ui.button>
                    @endif
                    <x-ui.button variant="ghost" size="sm" wire:click="openHistory({{ $document->id }})">Historial</x-ui.button>
                    @if ($document->latestVersion)
                        <a href="{{ route('documents.download', $document) }}"
                           class="rounded-lg px-2.5 py-1.5 text-xs font-medium text-indigo-600 hover:bg-slate-100 dark:text-indigo-400 dark:hover:bg-slate-800">Descargar</a>
                    @endif
                    @can('update', $document)
                        <x-ui.button variant="ghost" size="sm" wire:click="openNewVersion({{ $document->id }})">Nueva versión</x-ui.button>
                    @endcan
                    @can('delete', $document)
                        <x-ui.button variant="ghost" size="sm" wire:click="deleteDocument({{ $document->id }})" wire:confirm="¿Eliminar «{{ $document->name }}» y todas sus versiones?">
                            <span class="text-rose-600 dark:text-rose-400">Eliminar</span>
                        </x-ui.button>
                    @endcan
                </div>
            </div>
        @empty
            <x-ui.empty-state title="Sin archivos" description="Carga contratos, manuales, mockups y demás documentación del proyecto." />
        @endforelse
    </div>

    {{ $documents->links() }}

    {{-- Modal cargar archivo --}}
    <x-ui.modal name="document-form" title="Cargar archivo" max-width="lg">
        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Archivo</label>
                <input type="file" wire:model="file"
                       class="block w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-slate-700 dark:file:text-slate-200">
                @error('file') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                <div wire:loading wire:target="file" class="mt-1 text-xs text-slate-500">Subiendo…</div>
            </div>
            <x-ui.input label="Nombre" wire:model="name" name="name" required />
            <x-ui.select label="Categoría" wire:model="category" name="category">
                @foreach ($categories as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-ui.select>
            <x-ui.textarea label="Descripción" wire:model="description" name="description" rows="2" />
            <x-ui.textarea label="Notas de la versión" wire:model="notes" name="notes" rows="2" />

            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button variant="secondary" @click="$dispatch('close-modal', 'document-form')">Cancelar</x-ui.button>
                <x-ui.button type="submit" wire:loading.attr="disabled">Cargar</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Modal nueva versión --}}
    <x-ui.modal name="document-version-form" title="Cargar nueva versión" max-width="lg">
        <form wire:submit="saveVersion" class="space-y-4">
            <p class="text-sm text-slate-500 dark:text-slate-400">Documento: <span class="font-medium text-slate-700 dark:text-slate-200">{{ $name }}</span></p>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Archivo</label>
                <input type="file" wire:model="file"
                       class="block w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-slate-700 dark:file:text-slate-200">
                @error('file') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                <div wire:loading wire:target="file" class="mt-1 text-xs text-slate-500">Subiendo…</div>
            </div>
            <x-ui.textarea label="Notas de la versión" wire:model="notes" name="notes" rows="2" placeholder="¿Qué cambió en esta versión?" />

            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button variant="secondary" @click="$dispatch('close-modal', 'document-version-form')">Cancelar</x-ui.button>
                <x-ui.button type="submit" wire:loading.attr="disabled">Cargar versión</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    {{-- Modal historial --}}
    <x-ui.modal name="document-history" :title="'Historial — '.($historyDocument?->name ?? '')" max-width="xl">
        @if ($historyDocument)
            <ol class="space-y-2">
                @foreach ($historyDocument->versions as $version)
                    <li class="flex flex-wrap items-center gap-3 rounded-xl border border-slate-200 p-3 dark:border-slate-700" wire:key="version-{{ $version->id }}">
                        <x-ui.badge>v{{ $version->version }}</x-ui.badge>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">{{ $version->file_name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $version->uploader->name }} · {{ $version->created_at->translatedFormat('d M Y H:i') }}
                                · {{ Illuminate\Support\Number::fileSize($version->file_size) }}
                            </p>
                            @if ($version->notes)
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $version->notes }}</p>
                            @endif
                        </div>
                        <a href="{{ route('documents.versions.download', [$historyDocument, $version]) }}"
                           class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400">Descargar</a>
                    </li>
                @endforeach
            </ol>
        @endif
    </x-ui.modal>

    {{-- Modal vista previa --}}
    <x-ui.modal name="document-preview" :title="'Vista previa — '.($previewDocument?->name ?? '')" max-width="4xl">
        @if ($previewDocument && $previewDocument->latestVersion)
            @if (str_starts_with((string) $previewDocument->latestVersion->mime_type, 'image/'))
                <img src="{{ $previewDocument->latestVersion->fileUrl() }}" alt="{{ $previewDocument->name }}" class="max-h-[32rem] w-full rounded-xl object-contain">
            @else
                <iframe src="{{ $previewDocument->latestVersion->fileUrl() }}" class="h-[32rem] w-full rounded-xl border border-slate-200 dark:border-slate-700" title="Vista previa"></iframe>
            @endif
        @endif
    </x-ui.modal>
</div>
