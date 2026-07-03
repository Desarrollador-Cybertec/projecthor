<?php

declare(strict_types=1);

namespace App\Domains\Projects\Services;

use App\Domains\Activities\Models\Activity;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;
use App\Support\Export\XlsxExporter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ProjectExportService
{
    public function __construct(
        private XlsxExporter $xlsx,
    ) {}

    /**
     * Full project report as PDF: summary, stages and activities.
     */
    public function reportPdf(Project $project): Response
    {
        $project->load(['responsible', 'members', 'stages', 'activities.responsible', 'activities.stage']);

        return Pdf::loadView('exports.project-report', ['project' => $project])
            ->setPaper('a4')
            ->download('proyecto-'.$project->slug.'.pdf');
    }

    /**
     * The project's activities as an Excel workbook.
     */
    public function activitiesXlsx(Project $project): BinaryFileResponse
    {
        $activities = $project->activities()
            ->with(['stage', 'responsible'])
            ->orderBy('position')
            ->get();

        return $this->xlsx->download(
            'actividades-'.$project->slug.'.xlsx',
            ['Actividad', 'Descripción', 'Etapa', 'Responsable', 'Prioridad', 'Estado', 'Creada', 'Finalizada'],
            $activities->map(fn (Activity $activity): array => [
                $activity->name,
                Str::limit((string) $activity->description, 200),
                $activity->stage?->name,
                $activity->responsible?->name,
                $activity->priority->label(),
                $activity->status->label(),
                $activity->created_at?->format('Y-m-d H:i'),
                $activity->completed_at?->format('Y-m-d H:i'),
            ]),
        );
    }

    /**
     * Every project visible to the user as an Excel workbook.
     */
    public function projectsXlsx(User $user): BinaryFileResponse
    {
        $projects = Project::query()
            ->visibleTo($user)
            ->with(['responsible', 'stages'])
            ->orderBy('name')
            ->get();

        return $this->xlsx->download(
            'proyectos.xlsx',
            ['Proyecto', 'Cliente', 'Responsable', 'Estado', 'Prioridad', 'Avance %', 'Inicio', 'Entrega'],
            $projects->map(fn (Project $project): array => [
                $project->name,
                $project->client_name,
                $project->responsible?->name,
                $project->status->label(),
                $project->priority->label(),
                $project->progress,
                $project->start_date?->format('Y-m-d'),
                $project->due_date?->format('Y-m-d'),
            ]),
        );
    }
}
