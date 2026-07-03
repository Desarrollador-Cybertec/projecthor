<?php

declare(strict_types=1);

namespace App\Livewire\Projects\Forms;

use App\Domains\Projects\DTOs\ProjectData;
use App\Domains\Projects\Enums\Priority;
use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Projects\Models\Project;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ProjectForm extends Form
{
    public string $name = '';

    public string $client_name = '';

    public string $description = '';

    public string $color = '#6366f1';

    public ?int $responsible_id = null;

    public string $start_date = '';

    public string $due_date = '';

    public string $priority = 'medium';

    public string $status = 'active';

    public string $production_url = '';

    public string $staging_url = '';

    public string $documentation_url = '';

    public string $repository_url = '';

    /** @var list<int> */
    public array $member_ids = [];

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'client_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'responsible_id' => ['required', 'integer', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'priority' => ['required', Rule::enum(Priority::class)],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'production_url' => ['nullable', 'url', 'max:255'],
            'staging_url' => ['nullable', 'url', 'max:255'],
            'documentation_url' => ['nullable', 'url', 'max:255'],
            'repository_url' => ['nullable', 'url', 'max:255'],
            'member_ids' => ['array'],
            'member_ids.*' => ['integer', Rule::exists('users', 'id')->whereNull('deleted_at')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationAttributes(): array
    {
        return [
            'name' => 'nombre',
            'client_name' => 'cliente',
            'description' => 'descripción',
            'color' => 'color',
            'responsible_id' => 'responsable',
            'start_date' => 'fecha de inicio',
            'due_date' => 'fecha de entrega',
            'priority' => 'prioridad',
            'status' => 'estado',
            'production_url' => 'URL de producción',
            'staging_url' => 'URL de pruebas',
            'documentation_url' => 'URL de documentación',
            'repository_url' => 'repositorio Git',
            'member_ids' => 'equipo',
        ];
    }

    public function fillFromProject(Project $project): void
    {
        $this->name = $project->name;
        $this->client_name = $project->client_name;
        $this->description = (string) $project->description;
        $this->color = $project->color;
        $this->responsible_id = $project->responsible_id;
        $this->start_date = $project->start_date?->toDateString() ?? '';
        $this->due_date = $project->due_date?->toDateString() ?? '';
        $this->priority = $project->priority->value;
        $this->status = $project->status->value;
        $this->production_url = (string) $project->production_url;
        $this->staging_url = (string) $project->staging_url;
        $this->documentation_url = (string) $project->documentation_url;
        $this->repository_url = (string) $project->repository_url;
        $this->member_ids = $project->members->pluck('id')->all();
    }

    public function toData(): ProjectData
    {
        $this->validate();

        return new ProjectData(
            name: $this->name,
            clientName: $this->client_name,
            responsibleId: (int) $this->responsible_id,
            priority: Priority::from($this->priority),
            status: ProjectStatus::from($this->status),
            description: $this->description !== '' ? $this->description : null,
            color: $this->color,
            startDate: $this->start_date !== '' ? $this->start_date : null,
            dueDate: $this->due_date !== '' ? $this->due_date : null,
            productionUrl: $this->production_url !== '' ? $this->production_url : null,
            stagingUrl: $this->staging_url !== '' ? $this->staging_url : null,
            documentationUrl: $this->documentation_url !== '' ? $this->documentation_url : null,
            repositoryUrl: $this->repository_url !== '' ? $this->repository_url : null,
            memberIds: array_map(intval(...), $this->member_ids),
        );
    }
}
