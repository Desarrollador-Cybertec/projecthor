<div class="relative" x-data="{ open: false }">
    <button type="button" @click="open = !open" aria-label="Notificaciones"
            class="relative rounded-lg p-2 text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800">
        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
        @if ($unreadCount > 0)
            <span class="absolute -right-0.5 -top-0.5 flex size-4.5 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="open" x-cloak @click.outside="open = false" x-transition.opacity
         class="absolute right-0 z-30 mt-2 w-80 rounded-xl border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-800">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-2.5 dark:border-slate-700">
            <span class="text-sm font-semibold">Notificaciones</span>
            @if ($unreadCount > 0)
                <button type="button" wire:click="markAllAsRead" class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400">Marcar todas leídas</button>
            @endif
        </div>
        <div class="max-h-80 overflow-y-auto">
            @forelse ($recent as $notification)
                <a href="{{ $notification->data['url'] ?? route('notifications.index') }}"
                   wire:click="markAsRead('{{ $notification->id }}')"
                   class="block border-b border-slate-100 px-4 py-3 text-sm hover:bg-slate-50 last:border-0 dark:border-slate-700/50 dark:hover:bg-slate-700/40 {{ $notification->read_at ? 'opacity-60' : '' }}">
                    <p class="font-medium">{{ $notification->data['title'] ?? 'Notificación' }}</p>
                    <p class="mt-0.5 line-clamp-2 text-xs text-slate-500 dark:text-slate-400">{{ $notification->data['message'] ?? '' }}</p>
                    <p class="mt-0.5 text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</p>
                </a>
            @empty
                <p class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">No tienes notificaciones.</p>
            @endforelse
        </div>
        <a href="{{ route('notifications.index') }}" wire:navigate class="block border-t border-slate-100 px-4 py-2.5 text-center text-sm font-medium text-indigo-600 hover:bg-slate-50 dark:border-slate-700 dark:text-indigo-400 dark:hover:bg-slate-700/40">
            Ver todas
        </a>
    </div>
</div>
