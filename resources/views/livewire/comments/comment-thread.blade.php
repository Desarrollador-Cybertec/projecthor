<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-semibold">Observaciones</h2>
        <x-ui.select wire:model.live="statusFilter" class="!w-44">
            <option value="">Todos los estados</option>
            @foreach ($statuses as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </x-ui.select>
    </div>

    {{-- Nueva observación --}}
    @can('create', [App\Domains\Comments\Models\Comment::class, $commentable])
        <form wire:submit="save" class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            @if ($replyTo)
                <div class="mb-2 flex items-center justify-between rounded-lg bg-indigo-50 px-3 py-1.5 text-xs text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">
                    <span>Respondiendo a una observación</span>
                    <button type="button" wire:click="cancelReply" class="font-medium hover:underline">Cancelar</button>
                </div>
            @endif
            <x-ui.textarea wire:model="content" name="content" rows="3" placeholder="Escribe una observación…" />
            <div class="mt-3 flex flex-wrap items-end justify-between gap-3">
                <div class="space-y-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Adjuntos</label>
                        <input type="file" wire:model="attachments" multiple
                               class="block text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-slate-700 hover:file:bg-slate-200 dark:file:bg-slate-700 dark:file:text-slate-200">
                        @error('attachments.*') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="attachments" class="mt-1 text-xs text-slate-500">Subiendo adjuntos…</div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Capturas</label>
                        <input type="file" wire:model="captures" accept="image/*" multiple
                               class="block text-sm text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-slate-700 hover:file:bg-slate-200 dark:file:bg-slate-700 dark:file:text-slate-200">
                        @error('captures.*') <p class="mt-1 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="captures" class="mt-1 text-xs text-slate-500">Subiendo capturas…</div>
                    </div>
                </div>
                <x-ui.button type="submit" size="sm" wire:loading.attr="disabled">Publicar</x-ui.button>
            </div>
        </form>
    @endcan

    {{-- Hilo --}}
    <div class="space-y-3">
        @forelse ($comments as $comment)
            <div class="rounded-2xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900" wire:key="comment-{{ $comment->id }}">
                <div class="flex items-start gap-3">
                    <x-ui.avatar :user="$comment->author" size="8" />
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-medium">{{ $comment->author->name }}</span>
                            <span class="text-xs text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                            <x-ui.badge :classes="$comment->status->badgeClasses()">{{ $comment->status->label() }}</x-ui.badge>
                        </div>
                        <p class="mt-1 whitespace-pre-line text-sm text-slate-700 dark:text-slate-300">{{ $comment->content }}</p>

                        @if ($comment->attachments->isNotEmpty())
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($comment->attachments as $attachment)
                                    <a href="{{ route('comments.attachments.download', $attachment) }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-1 text-xs text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" /></svg>
                                        {{ $attachment->file_name }}
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        @include('livewire.comments.partials.captures', ['comment' => $comment, 'thumb' => 'size-16'])

                        <div class="mt-2 flex flex-wrap items-center gap-3 text-xs">
                            @can('create', [App\Domains\Comments\Models\Comment::class, $commentable])
                                <button type="button" wire:click="startReply({{ $comment->id }})" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">Responder</button>
                            @endcan
                            @can('changeStatus', $comment)
                                <select wire:change="changeStatus({{ $comment->id }}, $event.target.value)"
                                        class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs dark:border-slate-600 dark:bg-slate-800">
                                    @foreach ($statuses as $value => $label)
                                        <option value="{{ $value }}" @selected($comment->status->value === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            @endcan
                            @can('delete', $comment)
                                <button type="button" x-on:click="$store.confirm.open({{ \Illuminate\Support\Js::from('¿Eliminar esta observación?') }}, () => $wire.deleteComment({{ $comment->id }}))"
                                        class="font-medium text-rose-600 hover:underline dark:text-rose-400">Eliminar</button>
                            @endcan
                        </div>

                        {{-- Respuestas --}}
                        @if ($comment->replies->isNotEmpty())
                            <div class="mt-3 space-y-3 border-l-2 border-slate-100 pl-4 dark:border-slate-800">
                                @foreach ($comment->replies as $reply)
                                    <div class="flex items-start gap-2" wire:key="reply-{{ $reply->id }}">
                                        <x-ui.avatar :user="$reply->author" size="6" />
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="text-xs font-medium">{{ $reply->author->name }}</span>
                                                <span class="text-xs text-slate-400">{{ $reply->created_at->diffForHumans() }}</span>
                                                @can('delete', $reply)
                                                    <button type="button" x-on:click="$store.confirm.open({{ \Illuminate\Support\Js::from('¿Eliminar esta respuesta?') }}, () => $wire.deleteComment({{ $reply->id }}))"
                                                            class="text-xs font-medium text-rose-600 hover:underline dark:text-rose-400">Eliminar</button>
                                                @endcan
                                            </div>
                                            <p class="mt-0.5 whitespace-pre-line text-sm text-slate-700 dark:text-slate-300">{{ $reply->content }}</p>
                                            @if ($reply->attachments->isNotEmpty())
                                                <div class="mt-1.5 flex flex-wrap gap-2">
                                                    @foreach ($reply->attachments as $attachment)
                                                        <a href="{{ route('comments.attachments.download', $attachment) }}"
                                                           class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-1 text-xs text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">
                                                            {{ $attachment->file_name }}
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @include('livewire.comments.partials.captures', ['comment' => $reply, 'thumb' => 'size-12'])
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <x-ui.empty-state title="Sin observaciones" description="Sé el primero en dejar una observación." />
        @endforelse
    </div>
</div>
