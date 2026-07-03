<?php

declare(strict_types=1);

namespace App\Livewire\Stages;

use App\Domains\Projects\Models\Project;
use App\Domains\Stages\DTOs\StageData;
use App\Domains\Stages\Enums\StageStatus;
use App\Domains\Stages\Models\Stage;
use App\Domains\Stages\Services\StageService;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class StageList extends Component
{
    public Project $project;

    public ?int $stageId = null;

    public string $name = '';

    public string $description = '';

    public string $objective = '';

    public string $status = 'pending';

    public int $progress = 0;

    public string $starts_on = '';

    public string $estimated_end_on = '';

    public string $ended_on = '';

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'objective' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::enum(StageStatus::class)],
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'starts_on' => ['nullable', 'date'],
            'estimated_end_on' => ['nullable', 'date'],
            'ended_on' => ['nullable', 'date'],
        ];
    }

    public function openCreate(): void
    {
        $this->authorize('create', Stage::class);

        $this->resetForm();
        $this->dispatch('open-modal', 'stage-form');
    }

    public function openEdit(int $stageId): void
    {
        $stage = $this->project->stages()->findOrFail($stageId);

        $this->authorize('update', $stage);

        $this->stageId = $stage->id;
        $this->name = $stage->name;
        $this->description = (string) $stage->description;
        $this->objective = (string) $stage->objective;
        $this->status = $stage->status->value;
        $this->progress = $stage->progress;
        $this->starts_on = $stage->starts_on?->toDateString() ?? '';
        $this->estimated_end_on = $stage->estimated_end_on?->toDateString() ?? '';
        $this->ended_on = $stage->ended_on?->toDateString() ?? '';

        $this->resetValidation();
        $this->dispatch('open-modal', 'stage-form');
    }

    public function save(StageService $service): void
    {
        $this->validate();

        $data = new StageData(
            name: $this->name,
            status: StageStatus::from($this->status),
            description: $this->description !== '' ? $this->description : null,
            objective: $this->objective !== '' ? $this->objective : null,
            progress: $this->progress,
            startsOn: $this->starts_on !== '' ? $this->starts_on : null,
            estimatedEndOn: $this->estimated_end_on !== '' ? $this->estimated_end_on : null,
            endedOn: $this->ended_on !== '' ? $this->ended_on : null,
        );

        if ($this->stageId === null) {
            $this->authorize('create', Stage::class);

            $stage = $this->project->stages()->create([
                'name' => $data->name,
                'description' => $data->description,
                'objective' => $data->objective,
                'status' => StageStatus::Pending,
                'position' => ((int) $this->project->stages()->max('position')) + 1,
                'estimated_end_on' => $data->estimatedEndOn,
            ]);

            $service->update($stage, $data, auth()->user());
        } else {
            $stage = $this->project->stages()->findOrFail($this->stageId);
            $this->authorize('update', $stage);

            $service->update($stage, $data, auth()->user());
        }

        $this->dispatch('close-modal', 'stage-form');
        $this->dispatch('project-updated');
        $this->dispatch('toast', message: 'Etapa guardada correctamente.');
        $this->resetForm();
    }

    public function deleteStage(int $stageId): void
    {
        $stage = $this->project->stages()->findOrFail($stageId);

        $this->authorize('delete', $stage);

        $stage->delete();

        $this->dispatch('project-updated');
        $this->dispatch('toast', message: 'Etapa eliminada.');
    }

    public function render(): View
    {
        return view('livewire.stages.stage-list', [
            'stages' => $this->project->stages()->withCount('activities')->get(),
            'statuses' => StageStatus::options(),
        ]);
    }

    private function resetForm(): void
    {
        $this->reset(['stageId', 'name', 'description', 'objective', 'starts_on', 'estimated_end_on', 'ended_on']);
        $this->status = 'pending';
        $this->progress = 0;
        $this->resetValidation();
    }
}
