<?php

namespace Database\Factories;

use App\Domains\Projects\Models\Project;
use App\Domains\Timeline\Enums\TimelineEventType;
use App\Domains\Timeline\Models\TimelineEvent;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimelineEvent>
 */
class TimelineEventFactory extends Factory
{
    protected $model = TimelineEvent::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
            'type' => fake()->randomElement(TimelineEventType::cases()),
            'description' => fake()->sentence(),
            'created_at' => fake()->dateTimeBetween('-2 months', 'now'),
        ];
    }
}
