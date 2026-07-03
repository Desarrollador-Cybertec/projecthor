<?php

namespace Database\Factories;

use App\Domains\Comments\Models\Comment;
use App\Domains\Projects\Models\Project;
use App\Domains\Screenshots\Models\Screenshot;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Screenshot>
 */
class ScreenshotFactory extends Factory
{
    protected $model = Screenshot::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'comment_id' => Comment::factory(),
            'author_id' => User::factory(),
            'view_name' => null,
            'image_path' => 'screenshots/'.fake()->uuid().'.png',
            'description' => fake()->optional()->sentence(),
            'taken_at' => fake()->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
        ];
    }
}
