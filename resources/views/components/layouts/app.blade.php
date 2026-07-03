<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }} — {{ config('app.name') }}</title>
    <script>
        // Aplica el tema guardado. Se ejecuta antes del primer render y también
        // tras cada navegación wire:navigate, ya que Livewire remorfa el <html>
        // (sin la clase "dark") al servidor renderizarlo sin conocer localStorage.
        window.applyTheme = function () {
            const theme = localStorage.getItem('theme');
            const isDark = theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', isDark);
        };
        window.applyTheme();
        document.addEventListener('livewire:navigated', window.applyTheme);
        window.toggleTheme = function () {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        };
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-100 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    <div class="flex min-h-full" x-data="{ sidebarOpen: false }">
        {{-- Overlay móvil --}}
        <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden" @click="sidebarOpen = false"></div>

        {{-- Sidebar --}}
        <aside class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full transform bg-white shadow-lg transition-transform duration-200 lg:translate-x-0 dark:bg-slate-900 dark:shadow-slate-950/50"
               :class="{ 'translate-x-0': sidebarOpen }">
            <div class="flex h-16 items-center gap-2 border-b border-slate-200 px-6 dark:border-slate-800">
                <span class="flex size-8 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white">PF</span>
                <span class="text-lg font-semibold">ProjectFlow</span>
            </div>
            <nav class="space-y-1 p-4">
                @php
                    $navLink = 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition';
                    $navActive = 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300';
                    $navInactive = 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100';
                @endphp
                <a href="{{ route('dashboard') }}" wire:navigate class="{{ $navLink }} {{ request()->routeIs('dashboard') ? $navActive : $navInactive }}">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75" /></svg>
                    Dashboard
                </a>
                <a href="{{ route('projects.index') }}" wire:navigate class="{{ $navLink }} {{ request()->routeIs('projects.*') ? $navActive : $navInactive }}">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" /></svg>
                    Proyectos
                </a>
                <a href="{{ route('notifications.index') }}" wire:navigate class="{{ $navLink }} {{ request()->routeIs('notifications.*') ? $navActive : $navInactive }}">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                    Notificaciones
                </a>
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('users.index') }}" wire:navigate class="{{ $navLink }} {{ request()->routeIs('users.*') ? $navActive : $navInactive }}">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                        Usuarios
                    </a>
                @endif
            </nav>
        </aside>

        {{-- Contenido --}}
        <div class="flex min-h-screen w-full flex-col lg:pl-64">
            <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-slate-200 bg-white/80 px-4 backdrop-blur sm:px-6 dark:border-slate-800 dark:bg-slate-900/80">
                <button class="text-slate-500 lg:hidden" @click="sidebarOpen = true" aria-label="Abrir menú">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                </button>

                <livewire:search.global-search />

                <div class="ml-auto flex items-center gap-2">
                    <button type="button" onclick="toggleTheme()" aria-label="Cambiar tema"
                            class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800">
                        <svg class="size-5 dark:hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" /></svg>
                        <svg class="hidden size-5 dark:block" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" /></svg>
                    </button>

                    <livewire:notifications.bell />

                    <div class="relative" x-data="{ open: false }">
                        <button type="button" @click="open = !open" class="flex items-center gap-2 rounded-lg p-1.5 hover:bg-slate-100 dark:hover:bg-slate-800">
                            <x-ui.avatar :user="auth()->user()" size="8" />
                            <span class="hidden text-sm font-medium sm:block">{{ auth()->user()->name }}</span>
                        </button>
                        <div x-show="open" x-cloak @click.outside="open = false" x-transition.opacity
                             class="absolute right-0 mt-2 w-48 rounded-xl border border-slate-200 bg-white py-1 shadow-lg dark:border-slate-700 dark:bg-slate-800">
                            <a href="{{ route('profile.edit') }}" wire:navigate class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-700/50">Mi perfil</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-rose-600 hover:bg-slate-50 dark:text-rose-400 dark:hover:bg-slate-700/50">
                                    Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    <x-ui.confirm-dialog />

    {{-- Toasts --}}
    <div x-data="{ toasts: [] }"
         x-on:toast.window="const t = { id: Date.now(), ...$event.detail }; toasts.push(t); setTimeout(() => toasts = toasts.filter(i => i.id !== t.id), 4000)"
         class="pointer-events-none fixed bottom-4 right-4 z-50 flex w-80 flex-col gap-2">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="pointer-events-auto rounded-xl border px-4 py-3 text-sm shadow-lg"
                 :class="toast.type === 'error'
                    ? 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-500/30 dark:bg-rose-950 dark:text-rose-200'
                    : 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-950 dark:text-emerald-200'"
                 x-text="toast.message"></div>
        </template>
    </div>
</body>
</html>
