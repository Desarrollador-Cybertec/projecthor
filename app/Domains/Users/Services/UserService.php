<?php

declare(strict_types=1);

namespace App\Domains\Users\Services;

use App\Domains\Users\DTOs\UserData;
use App\Domains\Users\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function create(UserData $data): User
    {
        return User::query()->create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
            'role' => $data->role,
            'is_active' => $data->isActive,
        ]);
    }

    public function update(User $user, UserData $data): User
    {
        $attributes = [
            'name' => $data->name,
            'email' => $data->email,
            'role' => $data->role,
            'is_active' => $data->isActive,
        ];

        if ($data->password !== null && $data->password !== '') {
            $attributes['password'] = $data->password;
        }

        $user->update($attributes);

        return $user->refresh();
    }

    public function updateAvatar(User $user, UploadedFile $avatar): User
    {
        if ($user->avatar_path) {
            Storage::delete($user->avatar_path);
        }

        $user->update([
            'avatar_path' => $avatar->store('avatars', ['disk' => config('filesystems.default')]),
        ]);

        return $user->refresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
