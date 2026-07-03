<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <a href="{{ route('projects.index') }}" wire:navigate class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">← Volver a proyectos</a>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight">{{ $heading }}</h1>
    </div>

    <form wire:submit="save">
        <x-ui.card class="space-y-5">
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.input label="Nombre del proyecto" wire:model="form.name" name="form.name" required />
                <x-ui.input label="Cliente" wire:model="form.client_name" name="form.client_name" required />
            </div>

            <x-ui.textarea label="Descripción" wire:model="form.description" name="form.description" rows="4" />

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Color</label>
                    <input type="color" wire:model="form.color" class="h-10 w-full cursor-pointer rounded-lg border border-slate-300 bg-white p-1 dark:border-slate-600 dark:bg-slate-800">
                    @error('form.color') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">Logo</label>
                    <input type="file" wire:model="logo" accept="image/*"
                           class="block w-full text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-slate-700 dark:file:text-slate-200">
                    @error('logo') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    <div wire:loading wire:target="logo" class="mt-1 text-xs text-slate-500">Subiendo…</div>
                    @if ($logo && $logo->isPreviewable())
                        <img src="{{ $logo->temporaryUrl() }}" alt="" class="mt-2 size-12 rounded-lg object-cover">
                    @elseif ($existingLogoUrl)
                        <img src="{{ $existingLogoUrl }}" alt="" class="mt-2 size-12 rounded-lg object-cover">
                    @endif
                </div>
                <x-ui.select label="Responsable" wire:model="form.responsible_id" name="form.responsible_id" required>
                    <option value="">Seleccionar…</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-ui.input label="Fecha inicio" type="date" wire:model="form.start_date" name="form.start_date" />
                <x-ui.input label="Fecha entrega" type="date" wire:model="form.due_date" name="form.due_date" />
                <x-ui.select label="Prioridad" wire:model="form.priority" name="form.priority">
                    @foreach (\App\Domains\Projects\Enums\Priority::options() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select label="Estado" wire:model="form.status" name="form.status">
                    @foreach (\App\Domains\Projects\Enums\ProjectStatus::options() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.input label="URL producción" type="url" wire:model="form.production_url" name="form.production_url" placeholder="https://" />
                <x-ui.input label="URL pruebas" type="url" wire:model="form.staging_url" name="form.staging_url" placeholder="https://" />
                <x-ui.input label="URL documentación" type="url" wire:model="form.documentation_url" name="form.documentation_url" placeholder="https://" />
                <x-ui.input label="Repositorio Git" type="url" wire:model="form.repository_url" name="form.repository_url" placeholder="https://github.com/…" />
            </div>

            <div>
                <p class="mb-2 text-sm font-medium text-slate-700 dark:text-slate-300">Equipo de desarrollo</p>
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($users as $user)
                        <label class="flex items-center gap-3 rounded-lg border border-slate-200 px-3 py-2 text-sm hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">
                            <input type="checkbox" value="{{ $user->id }}" wire:model="form.member_ids"
                                   class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800">
                            <x-ui.avatar :user="$user" size="6" />
                            <span>{{ $user->name }}</span>
                            <x-ui.badge :classes="$user->role->badgeClasses()" class="ml-auto">{{ $user->role->label() }}</x-ui.badge>
                        </label>
                    @endforeach
                </div>
                @error('form.member_ids') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-200 pt-4 dark:border-slate-800">
                <a href="{{ route('projects.index') }}" wire:navigate
                   class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">
                    Cancelar
                </a>
                <x-ui.button type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="save">{{ $submitLabel }}</span>
                    <span wire:loading wire:target="save">Guardando…</span>
                </x-ui.button>
            </div>
        </x-ui.card>
    </form>
</div>
