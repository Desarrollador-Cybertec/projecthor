<?php

declare(strict_types=1);

namespace App\Livewire\Notifications;

use Illuminate\View\View;
use Livewire\Component;

class Bell extends Component
{
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
        $user = auth()->user();

        return view('livewire.notifications.bell', [
            'unreadCount' => $user->unreadNotifications()->count(),
            'recent' => $user->notifications()->latest()->limit(6)->get(),
        ]);
    }
}
