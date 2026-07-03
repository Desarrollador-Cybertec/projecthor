<?php

declare(strict_types=1);

namespace App\Livewire\Screenshots;

use App\Domains\Projects\Models\Project;
use App\Domains\Screenshots\DTOs\ScreenshotData;
use App\Domains\Screenshots\Models\Screenshot;
use App\Domains\Screenshots\Services\ScreenshotService;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class ScreenshotGallery extends Component
{
    use WithFileUploads;

    public Project $project;

    public string $groupBy = 'none';

    public ?int $detailScreenshotId = null;

    public mixed $image = null;

    public string $view_name = '';

    public string $module = '';

    public string $resolution = '';

    public string $platform = 'Web';

    public string $description = '';

    public string $notes = '';

    public string $version = '';

    public string $taken_at = '';

    public ?int $stage_id = null;

    public ?int $activity_id = null;

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'image' => ['required', 'image', 'max:10240'],
            'view_name' => ['required', 'string', 'max:255'],
            'module' => ['nullable', 'string', 'max:255'],
            'resolution' => ['nullable', 'string', 'max:50'],
            'platform' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'version' => ['nullable', 'string', 'max:20'],
            'taken_at' => ['nullable', 'date'],
            'stage_id' => ['nullable', 'integer', Rule::exists('stages', 'id')->where('project_id', $this->project->id)],
            'activity_id' => ['nullable', 'integer', Rule::exists('activities', 'id')->where('project_id', $this->project->id)],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'image' => 'imagen',
            'view_name' => 'vista',
            'module' => 'módulo',
            'resolution' => 'resolución',
            'platform' => 'plataforma',
            'description' => 'descripción',
            'notes' => 'observaciones',
            'version' => 'versión',
            'taken_at' => 'fecha',
            'stage_id' => 'etapa',
            'activity_id' => 'actividad',
        ];
    }

    public function openUpload(): void
    {
        $this->authorize('create', [Screenshot::class, $this->project]);

        $this->reset(['image', 'view_name', 'module', 'resolution', 'description', 'notes', 'version', 'taken_at', 'stage_id', 'activity_id']);
        $this->platform = 'Web';
        $this->resetValidation();
        $this->dispatch('open-modal', 'screenshot-form');
    }

    public function openDetail(int $screenshotId): void
    {
        $this->detailScreenshotId = $this->project->screenshots()->findOrFail($screenshotId)->id;
        $this->dispatch('open-modal', 'screenshot-detail');
    }

    public function save(ScreenshotService $service): void
    {
        $this->authorize('create', [Screenshot::class, $this->project]);

        $this->validate();

        $service->store($this->project, $this->image, new ScreenshotData(
            viewName: $this->view_name,
            module: $this->module !== '' ? $this->module : null,
            resolution: $this->resolution !== '' ? $this->resolution : null,
            platform: $this->platform !== '' ? $this->platform : null,
            description: $this->description !== '' ? $this->description : null,
            notes: $this->notes !== '' ? $this->notes : null,
            version: $this->version !== '' ? $this->version : null,
            takenAt: $this->taken_at !== '' ? $this->taken_at : null,
            stageId: $this->stage_id,
            activityId: $this->activity_id,
        ), auth()->user());

        $this->dispatch('close-modal', 'screenshot-form');
        $this->dispatch('toast', message: 'Captura agregada correctamente.');
        $this->reset(['image']);
    }

    public function deleteScreenshot(int $screenshotId, ScreenshotService $service): void
    {
        $screenshot = $this->project->screenshots()->findOrFail($screenshotId);

        $this->authorize('delete', $screenshot);

        $service->delete($screenshot);

        $this->dispatch('toast', message: 'Captura eliminada.');
    }

    public function render(): View
    {
        $screenshots = $this->project->screenshots()
            ->with(['stage', 'activity', 'author'])
            ->latest('taken_at')
            ->latest()
            ->get();

        /** @var Collection<string, Collection<int, Screenshot>> $grouped */
        $grouped = match ($this->groupBy) {
            'stage' => $screenshots->groupBy(fn (Screenshot $shot): string => $shot->stage?->name ?? 'Sin etapa'),
            'activity' => $screenshots->groupBy(fn (Screenshot $shot): string => $shot->activity?->name ?? 'Sin actividad'),
            'version' => $screenshots->groupBy(fn (Screenshot $shot): string => $shot->version ? "Versión {$shot->version}" : 'Sin versión'),
            default => collect(['Todas las capturas' => $screenshots]),
        };

        return view('livewire.screenshots.screenshot-gallery', [
            'grouped' => $grouped->filter(fn (Collection $group): bool => $group->isNotEmpty()),
            'stages' => $this->project->stages,
            'activities' => $this->project->activities()->orderBy('position')->get(),
            'detailScreenshot' => $this->detailScreenshotId
                ? $this->project->screenshots()->with(['stage', 'activity', 'author'])->find($this->detailScreenshotId)
                : null,
        ]);
    }
}
