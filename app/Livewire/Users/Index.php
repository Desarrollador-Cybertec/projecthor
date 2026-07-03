<?php

declare(strict_types=1);

namespace App\Livewire\Users;

use App\Domains\Users\DTOs\UserData;
use App\Domains\Users\Enums\UserRole;
use App\Domains\Users\Models\User;
use App\Domains\Users\Services\UserService;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $roleFilter = '';

    public ?int $userId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = 'developer';

    public bool $is_active = true;

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($this->userId)->withoutTrashed(),
            ],
            'password' => [$this->userId === null ? 'required' : 'nullable', 'string', Password::default()],
            'role' => ['required', Rule::enum(UserRole::class)],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'name' => 'nombre',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'role' => 'rol',
            'is_active' => 'activo',
        ];
    }

    public function openCreate(): void
    {
        $this->authorize('create', User::class);

        $this->reset(['userId', 'name', 'email', 'password']);
        $this->role = 'developer';
        $this->is_active = true;
        $this->resetValidation();
        $this->dispatch('open-modal', 'user-form');
    }

    public function openEdit(int $userId): void
    {
        $user = User::query()->findOrFail($userId);

        $this->authorize('update', $user);

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->role->value;
        $this->is_active = $user->is_active;

        $this->resetValidation();
        $this->dispatch('open-modal', 'user-form');
    }

    public function save(UserService $service): void
    {
        $this->validate();

        $data = new UserData(
            name: $this->name,
            email: $this->email,
            role: UserRole::from($this->role),
            isActive: $this->is_active,
            password: $this->password !== '' ? $this->password : null,
        );

        if ($this->userId === null) {
            $this->authorize('create', User::class);
            $service->create($data);
        } else {
            $user = User::query()->findOrFail($this->userId);
            $this->authorize('update', $user);
            $service->update($user, $data);
        }

        $this->dispatch('close-modal', 'user-form');
        $this->dispatch('toast', message: 'Usuario guardado correctamente.');
        $this->reset(['userId', 'name', 'email', 'password']);
    }

    public function toggleActive(int $userId): void
    {
        $user = User::query()->findOrFail($userId);

        $this->authorize('update', $user);

        if ($user->id === auth()->id()) {
            $this->dispatch('toast', type: 'error', message: 'No puedes desactivar tu propia cuenta.');

            return;
        }

        $user->update(['is_active' => ! $user->is_active]);

        $this->dispatch('toast', message: $user->is_active ? 'Usuario activado.' : 'Usuario desactivado.');
    }

    public function deleteUser(int $userId, UserService $service): void
    {
        $user = User::query()->findOrFail($userId);

        $this->authorize('delete', $user);

        $service->delete($user);

        $this->dispatch('toast', message: 'Usuario eliminado.');
    }

    public function render(): View
    {
        $users = User::query()
            ->withCount(['responsibleProjects', 'projects'])
            ->when($this->search !== '', function ($query) {
                $like = '%'.mb_strtolower(trim($this->search)).'%';
                $query->where(fn ($inner) => $inner
                    ->whereRaw('LOWER(name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$like]));
            })
            ->when(UserRole::tryFrom($this->roleFilter), fn ($query, $role) => $query->where('role', $role->value))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.users.index', [
            'users' => $users,
            'roles' => UserRole::options(),
        ])->title('Usuarios');
    }
}
