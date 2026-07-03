<?php

declare(strict_types=1);

namespace App\Livewire\Evidence;

use App\Domains\Evidence\DTOs\EvidenceData;
use App\Domains\Evidence\Enums\EvidenceType;
use App\Domains\Evidence\Models\Evidence;
use App\Domains\Evidence\Services\EvidenceService;
use App\Domains\Projects\Models\Project;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class EvidenceManager extends Component
{
    use WithFileUploads;
    use WithPagination;

    public Project $project;

    public string $search = '';

    public string $typeFilter = '';

    public string $activityFilter = '';

    public ?int $detailEvidenceId = null;

    // Subida de archivos
    /** @var array<int, mixed> */
    public array $files = [];

    public ?int $upload_activity_id = null;

    public string $upload_version = '1.0';

    public string $upload_description = '';

    // Evidencia de enlace
    public ?int $link_activity_id = null;

    public string $link_name = '';

    public string $link_url = '';

    public string $link_type = 'link';

    public string $link_version = '1.0';

    public string $link_description = '';

    public function openUpload(): void
    {
        $this->authorize('create', [Evidence::class, $this->project]);

        $this->reset(['files', 'upload_activity_id', 'upload_description']);
        $this->upload_version = '1.0';
        $this->resetValidation();
        $this->dispatch('open-modal', 'evidence-upload');
    }

    public function openLink(): void
    {
        $this->authorize('create', [Evidence::class, $this->project]);

        $this->reset(['link_activity_id', 'link_name', 'link_url', 'link_description']);
        $this->link_type = 'link';
        $this->link_version = '1.0';
        $this->resetValidation();
        $this->dispatch('open-modal', 'evidence-link');
    }

    public function openDetail(int $evidenceId): void
    {
        $this->detailEvidenceId = $this->project->evidences()->findOrFail($evidenceId)->id;
        $this->dispatch('open-modal', 'evidence-detail');
    }

    public function saveFiles(EvidenceService $service): void
    {
        $this->authorize('create', [Evidence::class, $this->project]);

        $this->validate([
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['file', 'max:51200'],
            'upload_activity_id' => ['required', 'integer', Rule::exists('activities', 'id')->where('project_id', $this->project->id)],
            'upload_version' => ['required', 'string', 'max:20'],
            'upload_description' => ['nullable', 'string', 'max:2000'],
        ], attributes: [
            'files' => 'archivos',
            'upload_activity_id' => 'actividad',
            'upload_version' => 'versión',
            'upload_description' => 'descripción',
        ]);

        $activity = $this->project->activities()->findOrFail($this->upload_activity_id);

        foreach ($this->files as $file) {
            $service->storeFile($activity, $file, new EvidenceData(
                name: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                version: $this->upload_version,
                description: $this->upload_description !== '' ? $this->upload_description : null,
            ), auth()->user());
        }

        $this->dispatch('close-modal', 'evidence-upload');
        $this->dispatch('project-updated');
        $this->dispatch('toast', message: 'Evidencias subidas correctamente.');
        $this->reset(['files', 'upload_description']);
    }

    public function saveLink(EvidenceService $service): void
    {
        $this->authorize('create', [Evidence::class, $this->project]);

        $this->validate([
            'link_activity_id' => ['required', 'integer', Rule::exists('activities', 'id')->where('project_id', $this->project->id)],
            'link_name' => ['required', 'string', 'max:255'],
            'link_url' => ['required', 'url', 'max:2048'],
            'link_type' => ['required', Rule::in(['link', 'figma', 'production'])],
            'link_version' => ['required', 'string', 'max:20'],
            'link_description' => ['nullable', 'string', 'max:2000'],
        ], attributes: [
            'link_activity_id' => 'actividad',
            'link_name' => 'nombre',
            'link_url' => 'URL',
            'link_type' => 'tipo',
            'link_version' => 'versión',
            'link_description' => 'descripción',
        ]);

        $activity = $this->project->activities()->findOrFail($this->link_activity_id);

        $service->storeLink($activity, new EvidenceData(
            name: $this->link_name,
            version: $this->link_version,
            description: $this->link_description !== '' ? $this->link_description : null,
            type: EvidenceType::from($this->link_type),
            url: $this->link_url,
        ), auth()->user());

        $this->dispatch('close-modal', 'evidence-link');
        $this->dispatch('project-updated');
        $this->dispatch('toast', message: 'Enlace registrado como evidencia.');
    }

    public function deleteEvidence(int $evidenceId, EvidenceService $service): void
    {
        $evidence = $this->project->evidences()->findOrFail($evidenceId);

        $this->authorize('delete', $evidence);

        $service->delete($evidence);

        $this->dispatch('toast', message: 'Evidencia eliminada.');
    }

    public function render(): View
    {
        $evidences = $this->project->evidences()
            ->with(['activity', 'author'])
            ->when($this->search !== '', function ($query) {
                $like = '%'.mb_strtolower(trim($this->search)).'%';
                $query->whereRaw('LOWER(name) LIKE ?', [$like]);
            })
            ->when(EvidenceType::tryFrom($this->typeFilter), fn ($query, $type) => $query->where('type', $type->value))
            ->when($this->activityFilter !== '', fn ($query) => $query->where('activity_id', (int) $this->activityFilter))
            ->latest()
            ->paginate(12);

        return view('livewire.evidence.evidence-manager', [
            'evidences' => $evidences,
            'activities' => $this->project->activities()->orderBy('position')->get(),
            'types' => EvidenceType::options(),
            'detailEvidence' => $this->detailEvidenceId
                ? $this->project->evidences()->with(['activity', 'author'])->find($this->detailEvidenceId)
                : null,
        ]);
    }
}
