<?php

namespace Database\Factories;

use App\Domains\Activities\Models\Activity;
use App\Domains\Evidence\Enums\EvidenceType;
use App\Domains\Evidence\Models\Evidence;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Evidence>
 */
class EvidenceFactory extends Factory
{
    protected $model = Evidence::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'activity_id' => Activity::factory(),
            'project_id' => fn (array $attributes) => Activity::find($attributes['activity_id'])->project_id,
            'author_id' => User::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'type' => EvidenceType::Link,
            'version' => fake()->randomElement(['1.0', '1.1', '2.0']),
            'url' => fake()->url(),
        ];
    }

    public function file(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => EvidenceType::Document,
            'url' => null,
            'file_path' => 'evidences/'.fake()->uuid().'.pdf',
            'file_name' => fake()->word().'.pdf',
            'file_size' => fake()->numberBetween(10_000, 5_000_000),
            'mime_type' => 'application/pdf',
        ]);
    }
}
