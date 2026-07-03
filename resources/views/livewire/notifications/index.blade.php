<div class="mx-auto max-w-3xl space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Notificaciones</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">{{ $unreadCount }} sin leer</p>
        </div>
        @if ($unreadCount > 0)
            <x-ui.button variant="secondary" size="sm" wire:click="markAllAsRead">Marcar todas como leídas</x-ui.button>
        @endif
    </div>

    <div class="space-y-2">
        @forelse ($notifications as $notification)
            <div class="flex items-start gap-3 rounded-2xl border p-4 {{ $notification->read_at
                    ? 'border-slate-200 bg-white opacity-70 dark:border-slate-800 dark:bg-slate-900'
                    : 'border-indigo-200 bg-indigo-50/50 dark:border-indigo-500/30 dark:bg-indigo-500/5' }}"
                 wire:key="notification-{{ $notification->id }}">
                <span class="mt-1 size-2 shrink-0 rounded-full {{ $notification->read_at ? 'bg-slate-300 dark:bg-slate-600' : 'bg-indigo-500' }}"></span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium">{{ $notification->data['title'] ?? 'Notificación' }}</p>
                    <p class="mt-0.5 text-sm text-slate-600 dark:text-slate-300">{{ $notification->data['message'] ?? '' }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $notification->created_at->translatedFormat('d M Y H:i') }} · {{ $notification->created_at->diffForHumans() }}</p>
                </div>
                <div class="flex shrink-0 flex-col items-end gap-1">
                    @if (isset($notification->data['url']))
                        <a href="{{ $notification->data['url'] }}" class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400">Ver</a>
                    @endif
                    @unless ($notification->read_at)
                        <button type="button" wire:click="markAsRead('{{ $notification->id }}')" class="text-xs text-slate-500 hover:underline dark:text-slate-400">Marcar leída</button>
                    @endunless
                </div>
            </div>
        @empty
            <x-ui.empty-state title="Sin notificaciones" description="Aquí verás las novedades de tus proyectos." />
        @endforelse
    </div>

    {{ $notifications->links() }}
</div>
