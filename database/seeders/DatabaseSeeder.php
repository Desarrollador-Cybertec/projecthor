<?php

namespace Database\Seeders;

use App\Domains\Activities\Enums\ActivityStatus;
use App\Domains\Activities\Models\Activity;
use App\Domains\Comments\Enums\CommentStatus;
use App\Domains\Comments\Models\Comment;
use App\Domains\Evidence\Models\Evidence;
use App\Domains\Files\Models\Document;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Services\ProjectService;
use App\Domains\Screenshots\Models\Screenshot;
use App\Domains\Stages\Enums\StageStatus;
use App\Domains\Stages\Models\Stage;
use App\Domains\Timeline\Enums\TimelineEventType;
use App\Domains\Timeline\Models\TimelineEvent;
use App\Domains\Users\Enums\UserRole;
use App\Domains\Users\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Administrador ProjectFlow',
            'email' => 'admin@projectflow.test',
        ]);

        $developers = User::factory()->count(4)->sequence(
            ['name' => 'Laura Gómez', 'email' => 'laura@projectflow.test'],
            ['name' => 'Carlos Ruiz', 'email' => 'carlos@projectflow.test'],
            ['name' => 'Ana Torres', 'email' => 'ana@projectflow.test'],
            ['name' => 'Miguel Ángel Díaz', 'email' => 'miguel@projectflow.test'],
        )->create(['role' => UserRole::Developer]);

        Project::factory()
            ->count(6)
            ->sequence(fn ($sequence) => ['responsible_id' => $sequence->index < 3 ? $admin->id : $developers->random()->id])
            ->create()
            ->each(function (Project $project, int $index) use ($admin, $developers): void {
                $team = $developers->random(2)->values();
                $project->members()->sync($team->pluck('id')->push($admin->id)->unique()->all());

                $this->seedStages($project, $index);
                $this->seedActivities($project, $team);
                $this->seedExtras($project, $admin);
            });

        $finished = Project::factory()
            ->completed()
            ->create([
                'name' => 'Portal Corporativo Acme',
                'client_name' => 'Acme S.A.S.',
                'responsible_id' => $admin->id,
            ]);

        $finished->members()->sync($developers->take(2)->pluck('id')->all());

        foreach (ProjectService::DEFAULT_STAGES as $position => $stage) {
            Stage::factory()->completed()->create([
                'project_id' => $finished->id,
                'name' => $stage['name'],
                'description' => $stage['description'],
                'objective' => $stage['objective'],
                'position' => $position,
            ]);
        }

        TimelineEvent::factory()->create([
            'project_id' => $finished->id,
            'user_id' => $admin->id,
            'type' => TimelineEventType::ProjectFinished,
            'description' => 'El proyecto fue finalizado.',
        ]);
    }

    private function seedStages(Project $project, int $index): void
    {
        // Vary how far along each seeded project is so the dashboard has variety.
        $completedStages = min($index, 4);

        foreach (ProjectService::DEFAULT_STAGES as $position => $stage) {
            $factory = Stage::factory();

            if ($position < $completedStages) {
                $factory = $factory->completed();
            } elseif ($position === $completedStages) {
                $factory = $factory->inProgress();
            }

            $factory->create([
                'project_id' => $project->id,
                'name' => $stage['name'],
                'description' => $stage['description'],
                'objective' => $stage['objective'],
                'position' => $position,
            ]);
        }
    }

    /**
     * @param  Collection<int, User>  $team
     */
    private function seedActivities(Project $project, Collection $team): void
    {
        $currentStage = $project->stages()->where('status', StageStatus::InProgress->value)->first();

        Activity::factory()
            ->count(8)
            ->sequence(fn ($sequence) => [
                'position' => $sequence->index,
                'responsible_id' => $team[$sequence->index % $team->count()]->id,
                'status' => match ($sequence->index % 4) {
                    0 => ActivityStatus::Finished,
                    1 => ActivityStatus::InDevelopment,
                    2 => ActivityStatus::InReview,
                    default => ActivityStatus::Pending,
                },
                'completed_at' => $sequence->index % 4 === 0 ? now()->subDays($sequence->index + 1) : null,
            ])
            ->create([
                'project_id' => $project->id,
                'stage_id' => $currentStage?->id,
            ]);

        $currentStage?->recalculateProgress();
    }

    private function seedExtras(Project $project, User $admin): void
    {
        $activities = $project->activities;

        $activities->take(3)->each(function (Activity $activity) use ($admin): void {
            Evidence::factory()->create([
                'project_id' => $activity->project_id,
                'activity_id' => $activity->id,
                'author_id' => $admin->id,
            ]);

            Comment::factory()->on($activity)->create([
                'author_id' => $activity->responsible_id ?? $admin->id,
                'status' => CommentStatus::Open,
            ]);
        });

        Screenshot::factory()->count(3)->create([
            'project_id' => $project->id,
            'activity_id' => $activities->first()?->id,
            'author_id' => $admin->id,
        ]);

        Document::factory()->count(2)->withVersion()->create([
            'project_id' => $project->id,
            'uploaded_by' => $admin->id,
        ]);

        Comment::factory()->on($project)->create(['author_id' => $admin->id]);

        TimelineEvent::factory()->create([
            'project_id' => $project->id,
            'user_id' => $admin->id,
            'type' => TimelineEventType::ProjectCreated,
            'description' => 'El proyecto fue creado.',
            'created_at' => $project->created_at,
        ]);
    }
}
