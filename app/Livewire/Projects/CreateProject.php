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

class CreateProject extends Component
{
    use WithFileUploads;

    public ProjectForm $form;

    public mixed $logo = null;

    public function mount(): void
    {
        $this->authorize('create', Project::class);
        $this->form->responsible_id = auth()->id();
    }

    public function save(ProjectService $service): void
    {
        $this->authorize('create', Project::class);

        $this->validate(['logo' => ['nullable', 'image', 'max:2048']]);

        $project = $service->create($this->form->toData(), $this->logo, auth()->user());

        session()->flash('status', 'Proyecto creado correctamente.');

        $this->redirectRoute('projects.show', $project, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.projects.project-form-page', [
            'users' => User::query()->active()->orderBy('name')->get(),
            'heading' => 'Nuevo proyecto',
            'submitLabel' => 'Crear proyecto',
            'existingLogoUrl' => null,
        ])->title('Nuevo proyecto');
    }
}
