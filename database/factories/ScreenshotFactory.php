<?php

namespace Database\Factories;

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
            'stage_id' => null,
            'activity_id' => null,
            'author_id' => User::factory(),
            'view_name' => fake()->randomElement(['Login', 'Dashboard', 'Listado', 'Detalle', 'Formulario', 'Reporte']),
            'module' => fake()->randomElement(['Autenticación', 'Usuarios', 'Reportes', 'Configuración', 'Facturación']),
            'resolution' => fake()->randomElement(['1920x1080', '1366x768', '390x844', '768x1024']),
            'platform' => fake()->randomElement(['Web', 'Móvil', 'Tablet']),
            'image_path' => 'screenshots/'.fake()->uuid().'.png',
            'description' => fake()->sentence(),
            'notes' => fake()->optional()->sentence(),
            'version' => fake()->randomElement(['1.0', '1.1', '2.0']),
            'taken_at' => fake()->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
        ];
    }
}
