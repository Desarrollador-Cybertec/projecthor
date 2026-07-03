<?php

declare(strict_types=1);

namespace App\Livewire\Projects;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Services\ProjectService;
use App\Domains\Users\Models\User;
use App\Livewire\Projects\Forms\ProjectForm;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditProject extends Component
{
    use WithFileUploads;

    public Project $project;

    public ProjectForm $form;

    public mixed $logo = null;

    public function mount(Project $project): void
    {
        $this->authorize('update', $project);

        $this->project = $project;
        $this->form->fillFromProject($project);
    }

    public function save(ProjectService $service): void
    {
        $this->authorize('update', $this->project);

        $this->validate(['logo' => ['nullable', 'image', 'max:2048']]);

        $service->update($this->project, $this->form->toData(), $this->logo);

        session()->flash('status', 'Proyecto actualizado correctamente.');

        $this->redirectRoute('projects.show', $this->project->refresh(), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.projects.project-form-page', [
            'users' => User::query()->active()->orderBy('name')->get(),
            'heading' => 'Editar proyecto',
            'submitLabel' => 'Guardar cambios',
            'existingLogoUrl' => $this->project->logoUrl(),
        ])->title('Editar proyecto');
    }
}
