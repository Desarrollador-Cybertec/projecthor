<?php

declare(strict_types=1);

namespace App\Livewire\Projects;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Services\ProjectService;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class Show extends Component
{
    public Project $project;

    #[Url]
    public string $tab = 'resumen';

    /** @var list<string> */
    public const array TABS = [
        'resumen',
        'etapas',
        'actividades',
        'evidencias',
        'capturas',
        'archivos',
        'observaciones',
        'timeline',
    ];

    public function mount(Project $project): void
    {
        $this->authorize('view', $project);

        $this->project = $project;

        if (! in_array($this->tab, self::TABS, true)) {
            $this->tab = 'resumen';
        }
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, self::TABS, true)) {
            $this->tab = $tab;
        }
    }

    public function finish(ProjectService $service): void
    {
        $this->authorize('finish', $this->project);

        $service->finish($this->project, auth()->user());

        $this->project->refresh();
        $this->dispatch('toast', message: 'Proyecto finalizado.');
    }

    public function delete(ProjectService $service): void
    {
        $this->authorize('delete', $this->project);

        $service->delete($this->project);

        session()->flash('status', 'Proyecto eliminado.');

        $this->redirectRoute('projects.index', navigate: true);
    }

    #[On('project-updated')]
    public function refreshProject(): void
    {
        $this->project->refresh();
    }

    public function render(): View
    {
        $this->project->load(['responsible', 'members', 'stages']);

        return view('livewire.projects.show')->title($this->project->name);
    }
}
