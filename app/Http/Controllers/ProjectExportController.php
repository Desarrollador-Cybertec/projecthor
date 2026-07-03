<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Services\ProjectExportService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ProjectExportController extends Controller
{
    public function __construct(
        private readonly ProjectExportService $exports,
    ) {}

    public function reportPdf(Project $project): Response
    {
        abort_unless(auth()->user()->can('export', $project), 403);

        return $this->exports->reportPdf($project);
    }

    public function activitiesXlsx(Project $project): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('export', $project), 403);

        return $this->exports->activitiesXlsx($project);
    }

    public function projectsXlsx(): BinaryFileResponse
    {
        return $this->exports->projectsXlsx(auth()->user());
    }
}
