<?php

declare(strict_types=1);

namespace App\Livewire\Timeline;

use App\Domains\Projects\Models\Project;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ProjectTimeline extends Component
{
    use WithPagination;

    public Project $project;

    public function render(): View
    {
        $events = $this->project->timelineEvents()
            ->with('user')
            ->paginate(20);

        return view('livewire.timeline.project-timeline', [
            'events' => $events,
        ]);
    }
}
