<?php

declare(strict_types=1);

namespace App\Livewire\Search;

use App\Domains\Activities\Models\Activity;
use App\Domains\Files\Models\Document;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $query = '';

    public function render(): View
    {
        /** @var User $user */
        $user = auth()->user();

        $term = trim($this->query);

        $projects = collect();
        $activities = collect();
        $documents = collect();

        if (mb_strlen($term) >= 2) {
            $visibleProjectIds = Project::query()->visibleTo($user)->select('id');

            $projects = Project::query()
                ->visibleTo($user)
                ->search($term)
                ->limit(5)
                ->get();

            $activities = Activity::query()
                ->whereIn('project_id', $visibleProjectIds)
                ->search($term)
                ->with('project')
                ->limit(5)
                ->get();

            $documents = Document::query()
                ->whereIn('project_id', $visibleProjectIds)
                ->search($term)
                ->with('project')
                ->limit(5)
                ->get();
        }

        return view('livewire.search.global-search', [
            'projects' => $projects,
            'activities' => $activities,
            'documents' => $documents,
            'hasResults' => $this->hasAny($projects, $activities, $documents),
        ]);
    }

    private function hasAny(Collection ...$collections): bool
    {
        foreach ($collections as $collection) {
            if ($collection->isNotEmpty()) {
                return true;
            }
        }

        return false;
    }
}
