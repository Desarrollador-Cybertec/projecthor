<?php

namespace Database\Factories;

use App\Domains\Projects\Enums\Priority;
use App\Domains\Projects\Enums\ProjectStatus;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->catchPhrase();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(6)),
            'client_name' => fake()->company(),
            'color' => fake()->randomElement(['#6366f1', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']),
            'description' => fake()->paragraph(),
            'responsible_id' => User::factory(),
            'start_date' => fake()->dateTimeBetween('-3 months', 'now')->format('Y-m-d'),
            'due_date' => fake()->dateTimeBetween('+1 month', '+6 months')->format('Y-m-d'),
            'priority' => fake()->randomElement(Priority::cases()),
            'status' => ProjectStatus::Active,
            'production_url' => fake()->optional()->url(),
            'staging_url' => fake()->optional()->url(),
            'documentation_url' => fake()->optional()->url(),
            'repository_url' => 'https://github.com/'.fake()->userName().'/'.Str::slug($name),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::Completed,
            'finished_at' => now(),
        ]);
    }
}
