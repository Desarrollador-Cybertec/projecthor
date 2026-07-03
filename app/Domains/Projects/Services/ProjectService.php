<?php

declare(strict_types=1);

namespace App\Domains\Projects\Services;

use App\Domains\Projects\DTOs\ProjectData;
use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Projects\Events\ProjectCreated;
use App\Domains\Projects\Events\ProjectFinished;
use App\Domains\Projects\Models\Project;
use App\Domains\Stages\Enums\StageStatus;
use App\Domains\Users\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectService
{
    /**
     * The stage flow every new project starts with.
     *
     * @var list<array{name: string, description: string, objective: string}>
     */
    public const array DEFAULT_STAGES = [
        ['name' => 'Planeación', 'description' => 'Levantamiento de requerimientos y planificación del proyecto.', 'objective' => 'Definir el alcance y el plan de trabajo.'],
        ['name' => 'Diseño', 'description' => 'Diseño de la experiencia, interfaz y arquitectura.', 'objective' => 'Entregar mockups y arquitectura aprobados.'],
        ['name' => 'Desarrollo', 'description' => 'Construcción de las funcionalidades del proyecto.', 'objective' => 'Implementar todas las funcionalidades planificadas.'],
        ['name' => 'Pruebas', 'description' => 'Pruebas funcionales, de integración y de aceptación.', 'objective' => 'Garantizar la calidad del producto.'],
        ['name' => 'Implementación', 'description' => 'Despliegue en el ambiente de producción.', 'objective' => 'Poner el sistema en manos del cliente.'],
        ['name' => 'Finalizado', 'description' => 'Cierre del proyecto y entrega de documentación.', 'objective' => 'Formalizar la entrega y el cierre.'],
    ];

    public function create(ProjectData $data, ?UploadedFile $logo = null, ?User $actor = null): Project
    {
        $project = DB::transaction(function () use ($data, $logo): Project {
            $project = Project::query()->create([
                'name' => $data->name,
                'slug' => $this->uniqueSlug($data->name),
                'client_name' => $data->clientName,
                'description' => $data->description,
                'color' => $data->color ?? '#6366f1',
                'responsible_id' => $data->responsibleId,
                'start_date' => $data->startDate,
                'due_date' => $data->dueDate,
                'priority' => $data->priority,
                'status' => $data->status,
                'production_url' => $data->productionUrl,
                'staging_url' => $data->stagingUrl,
                'documentation_url' => $data->documentationUrl,
                'repository_url' => $data->repositoryUrl,
                'logo_path' => $logo?->store('projects/logos', ['disk' => config('filesystems.default')]),
            ]);

            $project->members()->sync($data->memberIds);

            foreach (self::DEFAULT_STAGES as $position => $stage) {
                $project->stages()->create([
                    ...$stage,
                    'status' => StageStatus::Pending,
                    'position' => $position,
                ]);
            }

            return $project;
        });

        ProjectCreated::dispatch($project, $actor);

        return $project;
    }

    public function update(Project $project, ProjectData $data, ?UploadedFile $logo = null): Project
    {
        DB::transaction(function () use ($project, $data, $logo): void {
            $attributes = [
                'name' => $data->name,
                'client_name' => $data->clientName,
                'description' => $data->description,
                'color' => $data->color ?? $project->color,
                'responsible_id' => $data->responsibleId,
                'start_date' => $data->startDate,
                'due_date' => $data->dueDate,
                'priority' => $data->priority,
                'status' => $data->status,
                'production_url' => $data->productionUrl,
                'staging_url' => $data->stagingUrl,
                'documentation_url' => $data->documentationUrl,
                'repository_url' => $data->repositoryUrl,
            ];

            if ($logo !== null) {
                if ($project->logo_path) {
                    Storage::delete($project->logo_path);
                }

                $attributes['logo_path'] = $logo->store('projects/logos', ['disk' => config('filesystems.default')]);
            }

            $project->update($attributes);
            $project->members()->sync($data->memberIds);
        });

        return $project->refresh();
    }

    public function finish(Project $project, ?User $actor = null): Project
    {
        $project->update([
            'status' => ProjectStatus::Completed,
            'finished_at' => now(),
        ]);

        ProjectFinished::dispatch($project, $actor);

        return $project->refresh();
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $suffix = 1;

        while (Project::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.++$suffix;
        }

        return $slug;
    }
}
