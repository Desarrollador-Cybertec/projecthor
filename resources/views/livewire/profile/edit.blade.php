<div class="mx-auto max-w-2xl space-y-6">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">Mi perfil</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">Actualiza tu información personal y contraseña.</p>
    </div>

    <x-ui.card title="Información personal">
        <form wire:submit="updateProfile" class="space-y-4">
            <div class="flex items-center gap-4">
                @if ($avatar && $avatar->isPreviewable())
                    <img src="{{ $avatar->temporaryUrl() }}" alt="" class="size-16 rounded-full object-cover">
                @else
                    <x-ui.avatar :user="$user" size="12" />
                @endif
                <div>
                    <input type="file" wire:model="avatar" accept="image/*"
                           class="block text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-slate-700 hover:file:bg-slate-200 dark:file:bg-slate-700 dark:file:text-slate-200">
                    @error('avatar') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                    <div wire:loading wire:target="avatar" class="mt-1 text-xs text-slate-500">Subiendo…</div>
                </div>
            </div>

            <x-ui.input label="Nombre" wire:model="name" name="name" required />
            <x-ui.input label="Correo electrónico" type="email" wire:model="email" name="email" required />

            <div class="flex justify-end">
                <x-ui.button type="submit" wire:loading.attr="disabled">Guardar cambios</x-ui.button>
            </div>
        </form>
    </x-ui.card>

    <x-ui.card title="Cambiar contraseña">
        <form wire:submit="updatePassword" class="space-y-4">
            <x-ui.input label="Contraseña actual" type="password" wire:model="current_password" name="current_password" autocomplete="current-password" />
            <x-ui.input label="Nueva contraseña" type="password" wire:model="password" name="password" autocomplete="new-password" />
            <x-ui.input label="Confirmar nueva contraseña" type="password" wire:model="password_confirmation" name="password_confirmation" autocomplete="new-password" />

            <div class="flex justify-end">
                <x-ui.button type="submit" wire:loading.attr="disabled">Actualizar contraseña</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>
