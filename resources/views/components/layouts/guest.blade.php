<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Bienvenido' }} — {{ config('app.name') }}</title>
    <script>
        (function () {
            const theme = localStorage.getItem('theme');
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-slate-100 p-4 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    <div class="w-full max-w-md">
        <div class="mb-8 flex items-center justify-center gap-3">
            <span class="flex size-10 items-center justify-center rounded-xl bg-indigo-600 text-lg font-bold text-white">PF</span>
            <span class="text-2xl font-semibold">ProjectFlow</span>
        </div>
        {{ $slot }}
    </div>
</body>
</html>
