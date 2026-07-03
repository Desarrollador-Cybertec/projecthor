<?php

declare(strict_types=1);

namespace App\Livewire\Profile;

use App\Domains\Users\DTOs\UserData;
use App\Domains\Users\Models\User;
use App\Domains\Users\Services\UserService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public mixed $avatar = null;

    public function mount(): void
    {
        $user = $this->user();

        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function updateProfile(UserService $service): void
    {
        $user = $this->user();

        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)->withoutTrashed()],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ], attributes: ['name' => 'nombre', 'email' => 'correo electrónico', 'avatar' => 'avatar']);

        $service->update($user, new UserData(
            name: $this->name,
            email: $this->email,
            role: $user->role,
            isActive: $user->is_active,
        ));

        if ($this->avatar !== null) {
            $service->updateAvatar($user, $this->avatar);
            $this->reset('avatar');
        }

        $this->dispatch('toast', message: 'Perfil actualizado.');
    }

    public function updatePassword(): void
    {
        $user = $this->user();

        $this->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', Password::default()],
        ], attributes: ['current_password' => 'contraseña actual', 'password' => 'nueva contraseña']);

        if (! Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'La contraseña actual no es correcta.');

            return;
        }

        $user->update(['password' => $this->password]);

        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->dispatch('toast', message: 'Contraseña actualizada.');
    }

    public function render(): View
    {
        return view('livewire.profile.edit', [
            'user' => $this->user(),
        ])->title('Mi perfil');
    }

    private function user(): User
    {
        /** @var User */
        return auth()->user();
    }
}
