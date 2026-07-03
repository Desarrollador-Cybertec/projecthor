<div
    x-data
    x-show="$store.confirm.show"
    x-cloak
    x-on:keydown.escape.window="$store.confirm.cancel()"
    class="fixed inset-0 z-[60] flex items-center justify-center p-4"
>
    <div x-show="$store.confirm.show" x-transition.opacity class="fixed inset-0 bg-slate-900/60" @click="$store.confirm.cancel()"></div>

    <div x-show="$store.confirm.show" x-transition
         class="relative w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-700 dark:bg-slate-900">
        <p class="text-sm text-slate-700 dark:text-slate-200" x-text="$store.confirm.message"></p>
        <div class="mt-5 flex justify-end gap-3">
            <x-ui.button variant="secondary" size="sm" @click="$store.confirm.cancel()">Cancelar</x-ui.button>
            <button type="button" @click="$store.confirm.accept()"
                    class="inline-flex items-center justify-center gap-2 rounded-lg px-2.5 py-1.5 text-xs font-medium text-white transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                    :class="$store.confirm.danger ? 'bg-rose-600 hover:bg-rose-500' : 'bg-indigo-600 hover:bg-indigo-500'">
                Confirmar
            </button>
        </div>
    </div>
</div>
