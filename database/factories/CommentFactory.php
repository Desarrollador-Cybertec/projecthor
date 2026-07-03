<?php

namespace Database\Factories;

use App\Domains\Comments\Enums\CommentStatus;
use App\Domains\Comments\Models\Comment;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    protected $model = Comment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author_id' => User::factory(),
            'parent_id' => null,
            'commentable_type' => 'project',
            'commentable_id' => Project::factory(),
            'content' => fake()->paragraph(),
            'status' => CommentStatus::Open,
        ];
    }

    public function on(Model $commentable): static
    {
        return $this->state(fn (array $attributes) => [
            'commentable_type' => $commentable->getMorphClass(),
            'commentable_id' => $commentable->getKey(),
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CommentStatus::Resolved,
            'resolved_at' => now(),
        ]);
    }
}
