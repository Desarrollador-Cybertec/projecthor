<x-layouts.guest title="Iniciar sesión">
    <x-ui.card>
        <h1 class="mb-1 text-xl font-semibold">Iniciar sesión</h1>
        <p class="mb-6 text-sm text-slate-500 dark:text-slate-400">Ingresa tus credenciales para continuar.</p>

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <x-ui.input label="Correo electrónico" name="email" type="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-ui.input label="Contraseña" name="password" type="password" required autocomplete="current-password" />

            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-800">
                Recordarme
            </label>

            <x-ui.button type="submit" class="w-full">Ingresar</x-ui.button>
        </form>
    </x-ui.card>
</x-layouts.guest>
