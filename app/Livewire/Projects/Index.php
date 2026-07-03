<?php

declare(strict_types=1);

namespace App\Livewire\Projects;

use App\Domains\Projects\Enums\Priority;
use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $priority = '';

    #[Url]
    public string $sort = 'recent';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPriority(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        /** @var User $user */
        $user = auth()->user();

        $projects = Project::query()
            ->visibleTo($user)
            ->with(['responsible', 'stages'])
            ->when($this->search !== '', fn ($query) => $query->search($this->search))
            ->when(ProjectStatus::tryFrom($this->status), fn ($query, $status) => $query->where('status', $status->value))
            ->when(Priority::tryFrom($this->priority), fn ($query, $priority) => $query->where('priority', $priority->value))
            ->when($this->sort === 'name', fn ($query) => $query->orderBy('name'))
            ->when($this->sort === 'due_date', fn ($query) => $query->orderByRaw('due_date IS NULL')->orderBy('due_date'))
            ->when($this->sort === 'recent', fn ($query) => $query->latest('updated_at'))
            ->paginate(9);

        return view('livewire.projects.index', [
            'projects' => $projects,
            'statuses' => ProjectStatus::options(),
            'priorities' => Priority::options(),
        ])->title('Proyectos');
    }
}
