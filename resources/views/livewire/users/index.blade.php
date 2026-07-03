<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Usuarios</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Administra las cuentas del equipo.</p>
        </div>
        <x-ui.button wire:click="openCreate">Nuevo usuario</x-ui.button>
    </div>

    {{-- Filtros --}}
    <div class="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:grid-cols-2 dark:border-slate-800 dark:bg-slate-900">
        <x-ui.input placeholder="Buscar por nombre o correo…" wire:model.live.debounce.300ms="search" />
        <x-ui.select wire:model.live="roleFilter">
            <option value="">Todos los roles</option>
            @foreach ($roles as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </x-ui.select>
    </div>

    {{-- Tabla --}}
    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
            <thead>
                <tr class="text-left text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    <th class="px-4 py-3">Usuario</th>
                    <th class="px-4 py-3">Rol</th>
                    <th class="px-4 py-3">Proyectos</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($users as $user)
                    <tr wire:key="user-{{ $user->id }}">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <x-ui.avatar :user="$user" size="8" />
                                <div>
                                    <p class="font-medium">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <x-ui.badge :classes="$user->role->badgeClasses()">{{ $user->role->label() }}</x-ui.badge>
                        </td>
                        <td class="px-4 py-3 text-slate-500 dark:text-slate-400">
                            {{ $user->responsible_projects_count }} responsable · {{ $user->projects_count }} asignado
                        </td>
                        <td class="px-4 py-3">
                            <x-ui.badge :classes="$user->is_active
                                ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300'
                                : 'bg-slate-100 text-slate-600 dark:bg-slate-500/15 dark:text-slate-400'">
                                {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                            </x-ui.badge>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1">
                                <x-ui.button variant="ghost" size="sm" wire:click="openEdit({{ $user->id }})">Editar</x-ui.button>
                                @if ($user->id !== auth()->id())
                                    <x-ui.button variant="ghost" size="sm" wire:click="toggleActive({{ $user->id }})">
                                        {{ $user->is_active ? 'Desactivar' : 'Activar' }}
                                    </x-ui.button>
                                    @can('delete', $user)
                                        <x-ui.button variant="ghost" size="sm" x-on:click="$store.confirm.open({{ \Illuminate\Support\Js::from('¿Eliminar a '.$user->name.'?') }}, () => $wire.deleteUser({{ $user->id }}))">
                                            <span class="text-rose-600 dark:text-rose-400">Eliminar</span>
                                        </x-ui.button>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">No se encontraron usuarios.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links() }}

    {{-- Modal usuario --}}
    <x-ui.modal name="user-form" :title="$userId ? 'Editar usuario' : 'Nuevo usuario'" max-width="lg">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input label="Nombre" wire:model="name" name="name" required />
            <x-ui.input label="Correo electrónico" type="email" wire:model="email" name="email" required />
            <x-ui.input :label="$userId ? 'Contraseña (dejar en blanco para no cambiarla)' : 'Contraseña'" type="password" wire:model="password" name="password" />
            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.select label="Rol" wire:model="role" name="role">
                    @foreach ($roles as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                <label class="mt-6 flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                    <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800">
                    Cuenta activa
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <x-ui.button variant="secondary" @click="$dispatch('close-modal', 'user-form')">Cancelar</x-ui.button>
                <x-ui.button type="submit" wire:loading.attr="disabled">Guardar</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
