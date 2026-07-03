<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Domains\Dashboard\Services\DashboardService;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    public function render(DashboardService $dashboard): View
    {
        /** @var User $user */
        $user = auth()->user();

        $projects = Project::query()
            ->visibleTo($user)
            ->with(['responsible', 'stages'])
            ->latest('updated_at')
            ->limit(6)
            ->get();

        return view('livewire.dashboard.index', [
            ...$dashboard->overview($user),
            'projects' => $projects,
        ])->title('Dashboard');
    }
}
