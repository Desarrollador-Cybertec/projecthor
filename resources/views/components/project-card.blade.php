@props(['project'])

<a href="{{ route('projects.show', $project) }}" wire:navigate
   {{ $attributes->merge(['class' => 'group block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-slate-800 dark:bg-slate-900']) }}>
    <div class="mb-3 flex items-start gap-3">
        @if ($project->logoUrl())
            <img src="{{ $project->logoUrl() }}" alt="" class="size-10 rounded-xl object-cover">
        @else
            <span class="flex size-10 shrink-0 items-center justify-center rounded-xl text-sm font-bold text-white" style="background-color: {{ $project->color }}">
                {{ mb_substr($project->name, 0, 1) }}
            </span>
        @endif
        <div class="min-w-0 flex-1">
            <p class="truncate font-semibold group-hover:text-indigo-600 dark:group-hover:text-indigo-400">{{ $project->name }}</p>
            <p class="truncate text-sm text-slate-500 dark:text-slate-400">{{ $project->client_name }}</p>
        </div>
        <x-ui.badge :classes="$project->status->badgeClasses()">{{ $project->status->label() }}</x-ui.badge>
    </div>

    <x-ui.progress :value="$project->progress" class="mb-3" />

    <div class="flex items-center justify-between text-sm">
        <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400">
            <x-ui.avatar :user="$project->responsible" size="6" />
            <span class="truncate">{{ $project->responsible?->name }}</span>
        </div>
        @if ($stage = $project->currentStage())
            <x-ui.badge :classes="$stage->status->badgeClasses()">{{ $stage->name }}</x-ui.badge>
        @endif
    </div>

    <p class="mt-3 text-xs text-slate-400 dark:text-slate-500">
        Actualizado {{ $project->updated_at->diffForHumans() }}
    </p>
</a>
