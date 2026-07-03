<?php

declare(strict_types=1);

namespace App\Livewire\Activities;

use App\Domains\Activities\DTOs\ActivityData;
use App\Domains\Activities\Enums\ActivityStatus;
use App\Domains\Activities\Models\Activity;
use App\Domains\Activities\Services\ActivityService;
use App\Domains\Evidence\DTOs\EvidenceData;
use App\Domains\Evidence\Enums\EvidenceType;
use App\Domains\Evidence\Models\Evidence;
use App\Domains\Evidence\Services\EvidenceService;
use App\Domains\Projects\Enums\Priority;
use App\Domains\Projects\Models\Project;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ActivityList extends Component
{
    use WithFileUploads;
    use WithPagination;

    public Project $project;

    public string $search = '';

    public string $statusFilter = '';

    public string $stageFilter = '';

    public ?int $activityId = null;

    public ?int $detailActivityId = null;

    public string $name = '';

    public string $description = '';

    public ?int $stage_id = null;

    public ?int $responsible_id = null;

    public string $priority = 'medium';

    public string $status = 'pending';

    // Evidencias del detalle de actividad — subida de archivos
    /** @var array<int, mixed> */
    public array $evidenceFiles = [];

    public string $evidenceVersion = '1.0';

    public string $evidenceDescription = '';

    // Evidencias del detalle de actividad — enlace
    public string $evidenceLinkName = '';

    public string $evidenceLinkUrl = '';

    public string $evidenceLinkType = 'link';

    public string $evidenceLinkVersion = '1.0';

    public string $evidenceLinkDescription = '';

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'stage_id' => ['nullable', 'integer', Rule::exists('stages', 'id')->where('project_id', $this->project->id)],
            'responsible_id' => ['nullable', 'integer', Rule::exists('users', 'id')->whereNull('deleted_at')],
            'priority' => ['required', Rule::enum(Priority::class)],
            'status' => ['required', Rule::enum(ActivityStatus::class)],
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStageFilter(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->authorize('create', [Activity::class, $this->project]);

        $this->resetForm();
        $this->dispatch('open-modal', 'activity-form');
    }

    public function openEdit(int $activityId): void
    {
        $activity = $this->project->activities()->findOrFail($activityId);

        $this->authorize('update', $activity);

        $this->activityId = $activity->id;
        $this->name = $activity->name;
        $this->description = (string) $activity->description;
        $this->stage_id = $activity->stage_id;
        $this->responsible_id = $activity->responsible_id;
        $this->priority = $activity->priority->value;
        $this->status = $activity->status->value;

        $this->resetValidation();
        $this->dispatch('open-modal', 'activity-form');
    }

    public function openDetail(int $activityId): void
    {
        $this->detailActivityId = $this->project->activities()->findOrFail($activityId)->id;
        $this->resetEvidenceForms();
        $this->dispatch('open-modal', 'activity-detail');
    }

    public function saveEvidenceFiles(EvidenceService $service): void
    {
        $activity = $this->project->activities()->findOrFail($this->detailActivityId);

        $this->authorize('create', [Evidence::class, $this->project]);

        $this->validate([
            'evidenceFiles' => ['required', 'array', 'min:1', 'max:10'],
            'evidenceFiles.*' => ['file', 'max:51200'],
            'evidenceVersion' => ['required', 'string', 'max:20'],
            'evidenceDescription' => ['nullable', 'string', 'max:2000'],
        ], attributes: [
            'evidenceFiles' => 'archivos',
            'evidenceVersion' => 'versión',
            'evidenceDescription' => 'descripción',
        ]);

        foreach ($this->evidenceFiles as $file) {
            $service->storeFile($activity, $file, new EvidenceData(
                name: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                version: $this->evidenceVersion,
                description: $this->evidenceDescription !== '' ? $this->evidenceDescription : null,
            ), auth()->user());
        }

        $this->reset(['evidenceFiles', 'evidenceDescription']);
        $this->evidenceVersion = '1.0';
        $this->dispatch('project-updated');
        $this->dispatch('toast', message: 'Evidencias subidas correctamente.');
    }

    public function saveEvidenceLink(EvidenceService $service): void
    {
        $activity = $this->project->activities()->findOrFail($this->detailActivityId);

        $this->authorize('create', [Evidence::class, $this->project]);

        $this->validate([
            'evidenceLinkName' => ['required', 'string', 'max:255'],
            'evidenceLinkUrl' => ['required', 'url', 'max:2048'],
            'evidenceLinkType' => ['required', Rule::in(['link', 'figma', 'production'])],
            'evidenceLinkVersion' => ['required', 'string', 'max:20'],
            'evidenceLinkDescription' => ['nullable', 'string', 'max:2000'],
        ], attributes: [
            'evidenceLinkName' => 'nombre',
            'evidenceLinkUrl' => 'URL',
            'evidenceLinkType' => 'tipo',
            'evidenceLinkVersion' => 'versión',
            'evidenceLinkDescription' => 'descripción',
        ]);

        $service->storeLink($activity, new EvidenceData(
            name: $this->evidenceLinkName,
            version: $this->evidenceLinkVersion,
            description: $this->evidenceLinkDescription !== '' ? $this->evidenceLinkDescription : null,
            type: EvidenceType::from($this->evidenceLinkType),
            url: $this->evidenceLinkUrl,
        ), auth()->user());

        $this->resetEvidenceForms();
        $this->dispatch('project-updated');
        $this->dispatch('toast', message: 'Enlace registrado como evidencia.');
    }

    public function deleteEvidence(int $evidenceId, EvidenceService $service): void
    {
        $activity = $this->project->activities()->findOrFail($this->detailActivityId);
        $evidence = $activity->evidences()->findOrFail($evidenceId);

        $this->authorize('delete', $evidence);

        $service->delete($evidence);

        $this->dispatch('project-updated');
        $this->dispatch('toast', message: 'Evidencia eliminada.');
    }

    private function resetEvidenceForms(): void
    {
        $this->reset([
            'evidenceFiles',
            'evidenceDescription',
            'evidenceLinkName',
            'evidenceLinkUrl',
            'evidenceLinkDescription',
        ]);
        $this->evidenceVersion = '1.0';
        $this->evidenceLinkType = 'link';
        $this->evidenceLinkVersion = '1.0';
        $this->resetValidation();
    }

    public function save(ActivityService $service): void
    {
        $this->validate();

        $data = new ActivityData(
            name: $this->name,
            priority: Priority::from($this->priority),
            status: ActivityStatus::from($this->status),
            description: $this->description !== '' ? $this->description : null,
            stageId: $this->stage_id,
            responsibleId: $this->responsible_id,
        );

        if ($this->activityId === null) {
            $this->authorize('create', [Activity::class, $this->project]);
            $service->create($this->project, $data, auth()->user());
        } else {
            $activity = $this->project->activities()->findOrFail($this->activityId);
            $this->authorize('update', $activity);
            $service->update($activity, $data, auth()->user());
        }

        $this->dispatch('close-modal', 'activity-form');
        $this->dispatch('project-updated');
        $this->dispatch('toast', message: 'Actividad guardada correctamente.');
        $this->resetForm();
    }

    public function changeStatus(int $activityId, string $status, ActivityService $service): void
    {
        $activity = $this->project->activities()->findOrFail($activityId);

        $this->authorize('changeStatus', $activity);

        $service->changeStatus($activity, ActivityStatus::from($status), auth()->user());

        $this->dispatch('project-updated');
        $this->dispatch('toast', message: 'Estado actualizado.');
    }

    public function move(int $activityId, int $direction): void
    {
        $activity = $this->project->activities()->findOrFail($activityId);

        $this->authorize('update', $activity);

        $ordered = $this->project->activities()->orderBy('position')->pluck('id')->all();
        $index = array_search($activity->id, $ordered, true);
        $target = $index + ($direction >= 0 ? 1 : -1);

        if ($index === false || $target < 0 || $target >= count($ordered)) {
            return;
        }

        [$ordered[$index], $ordered[$target]] = [$ordered[$target], $ordered[$index]];

        app(ActivityService::class)->reorder($this->project, $ordered);
    }

    public function deleteActivity(int $activityId, ActivityService $service): void
    {
        $activity = $this->project->activities()->findOrFail($activityId);

        $this->authorize('delete', $activity);

        $service->delete($activity);

        $this->dispatch('project-updated');
        $this->dispatch('toast', message: 'Actividad eliminada.');
    }

    public function render(): View
    {
        $activities = $this->project->activities()
            ->with(['stage', 'responsible'])
            ->withCount(['evidences', 'comments'])
            ->when($this->search !== '', fn ($query) => $query->search($this->search))
            ->when(ActivityStatus::tryFrom($this->statusFilter), fn ($query, $status) => $query->where('status', $status->value))
            ->when($this->stageFilter !== '', fn ($query) => $query->where('stage_id', (int) $this->stageFilter))
            ->orderBy('position')
            ->paginate(10);

        return view('livewire.activities.activity-list', [
            'activities' => $activities,
            'stages' => $this->project->stages,
            'members' => $this->project->team(),
            'statuses' => ActivityStatus::options(),
            'priorities' => Priority::options(),
            'detailActivity' => $this->detailActivityId
                ? $this->project->activities()->with(['responsible', 'stage', 'evidences.author'])->find($this->detailActivityId)
                : null,
        ]);
    }

    private function resetForm(): void
    {
        $this->reset(['activityId', 'name', 'description', 'stage_id', 'responsible_id']);
        $this->priority = 'medium';
        $this->status = 'pending';
        $this->resetValidation();
    }
}
