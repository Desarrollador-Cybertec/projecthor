<?php

namespace Database\Factories;

use App\Domains\Files\Enums\DocumentCategory;
use App\Domains\Files\Models\Document;
use App\Domains\Files\Models\DocumentVersion;
use App\Domains\Projects\Models\Project;
use App\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'uploaded_by' => User::factory(),
            'category' => fake()->randomElement(DocumentCategory::cases()),
            'name' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function withVersion(int $version = 1): static
    {
        return $this->afterCreating(function (Document $document) use ($version): void {
            DocumentVersion::query()->create([
                'document_id' => $document->id,
                'version' => $version,
                'file_path' => 'documents/'.fake()->uuid().'.pdf',
                'file_name' => fake()->word().'.pdf',
                'file_size' => fake()->numberBetween(10_000, 2_000_000),
                'mime_type' => 'application/pdf',
                'uploaded_by' => $document->uploaded_by,
            ]);
        });
    }
}
