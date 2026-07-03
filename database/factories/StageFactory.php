<?php

namespace Database\Factories;

use App\Domains\Projects\Models\Project;
use App\Domains\Stages\Enums\StageStatus;
use App\Domains\Stages\Models\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stage>
 */
class StageFactory extends Factory
{
    protected $model = Stage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => fake()->randomElement(['Planeación', 'Diseño', 'Desarrollo', 'Pruebas', 'Implementación', 'Finalizado']),
            'description' => fake()->sentence(),
            'objective' => fake()->sentence(),
            'status' => StageStatus::Pending,
            'progress' => 0,
            'starts_on' => null,
            'estimated_end_on' => fake()->dateTimeBetween('+1 week', '+3 months')->format('Y-m-d'),
            'position' => fake()->numberBetween(0, 5),
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StageStatus::InProgress,
            'starts_on' => now()->toDateString(),
            'progress' => fake()->numberBetween(10, 80),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StageStatus::Completed,
            'starts_on' => now()->subMonth()->toDateString(),
            'ended_on' => now()->toDateString(),
            'progress' => 100,
        ]);
    }
}
