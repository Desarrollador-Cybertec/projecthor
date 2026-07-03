<?php

namespace App\Providers;

use App\Domains\Activities\Models\Activity;
use App\Domains\Activities\Policies\ActivityPolicy;
use App\Domains\Comments\Models\Comment;
use App\Domains\Comments\Models\CommentAttachment;
use App\Domains\Comments\Policies\CommentPolicy;
use App\Domains\Evidence\Models\Evidence;
use App\Domains\Evidence\Policies\EvidencePolicy;
use App\Domains\Files\Models\Document;
use App\Domains\Files\Models\DocumentVersion;
use App\Domains\Files\Policies\DocumentPolicy;
use App\Domains\Projects\Models\Project;
use App\Domains\Projects\Policies\ProjectPolicy;
use App\Domains\Screenshots\Models\Screenshot;
use App\Domains\Screenshots\Policies\ScreenshotPolicy;
use App\Domains\Stages\Models\Stage;
use App\Domains\Stages\Policies\StagePolicy;
use App\Domains\Timeline\Listeners\RecordTimelineEvents;
use App\Domains\Timeline\Models\TimelineEvent;
use App\Domains\Users\Listeners\SendProjectNotifications;
use App\Domains\Users\Models\User;
use App\Domains\Users\Policies\UserPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventSilentlyDiscardingAttributes(! $this->app->environment('production'));

        Relation::enforceMorphMap([
            'user' => User::class,
            'project' => Project::class,
            'stage' => Stage::class,
            'activity' => Activity::class,
            'evidence' => Evidence::class,
            'screenshot' => Screenshot::class,
            'comment' => Comment::class,
            'comment_attachment' => CommentAttachment::class,
            'document' => Document::class,
            'document_version' => DocumentVersion::class,
            'timeline_event' => TimelineEvent::class,
        ]);

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Project::class, ProjectPolicy::class);
        Gate::policy(Stage::class, StagePolicy::class);
        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(Evidence::class, EvidencePolicy::class);
        Gate::policy(Screenshot::class, ScreenshotPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);

        Event::subscribe(RecordTimelineEvents::class);
        Event::subscribe(SendProjectNotifications::class);
    }
}
