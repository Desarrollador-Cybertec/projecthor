<?php

namespace Database\Factories;

use App\Domains\Activities\Enums\ActivityStatus;
use App\Domains\Activities\Models\Activity;
use App\Domains\Projects\Enums\Priority;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'stage_id' => null,
            'name' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'responsible_id' => User::factory(),
            'priority' => fake()->randomElement(Priority::cases()),
            'status' => ActivityStatus::Pending,
            'position' => fake()->numberBetween(0, 20),
        ];
    }

    public function finished(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ActivityStatus::Finished,
            'completed_at' => now(),
        ]);
    }
}
