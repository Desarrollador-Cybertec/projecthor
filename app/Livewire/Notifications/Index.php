<?php

declare(strict_types=1);

namespace App\Livewire\Notifications;

use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function markAsRead(string $notificationId): void
    {
        auth()->user()
            ->unreadNotifications()
            ->where('id', $notificationId)
            ->first()
            ?->markAsRead();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    public function render(): View
    {
        return view('livewire.notifications.index', [
            'notifications' => auth()->user()->notifications()->latest()->paginate(15),
            'unreadCount' => auth()->user()->unreadNotifications()->count(),
        ])->title('Notificaciones');
    }
}
