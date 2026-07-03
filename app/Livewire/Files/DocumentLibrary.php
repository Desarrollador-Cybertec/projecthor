<?php

declare(strict_types=1);

namespace App\Livewire\Files;

use App\Domains\Files\DTOs\DocumentData;
use App\Domains\Files\Enums\DocumentCategory;
use App\Domains\Files\Models\Document;
use App\Domains\Files\Services\DocumentService;
use App\Domains\Projects\Models\Project;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class DocumentLibrary extends Component
{
    use WithFileUploads;
    use WithPagination;

    public Project $project;

    public string $search = '';

    public string $categoryFilter = '';

    public ?int $documentId = null;

    public ?int $historyDocumentId = null;

    public ?int $previewDocumentId = null;

    public mixed $file = null;

    public string $name = '';

    public string $category = 'technical_docs';

    public string $description = '';

    public string $notes = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function openUpload(): void
    {
        $this->authorize('create', [Document::class, $this->project]);

        $this->reset(['documentId', 'file', 'name', 'description', 'notes']);
        $this->category = 'technical_docs';
        $this->resetValidation();
        $this->dispatch('open-modal', 'document-form');
    }

    public function openNewVersion(int $documentId): void
    {
        $document = $this->project->documents()->findOrFail($documentId);

        $this->authorize('update', $document);

        $this->reset(['file', 'notes']);
        $this->documentId = $document->id;
        $this->name = $document->name;
        $this->category = $document->category->value;
        $this->description = (string) $document->description;
        $this->resetValidation();
        $this->dispatch('open-modal', 'document-version-form');
    }

    public function openHistory(int $documentId): void
    {
        $this->historyDocumentId = $this->project->documents()->findOrFail($documentId)->id;
        $this->dispatch('open-modal', 'document-history');
    }

    public function openPreview(int $documentId): void
    {
        $this->previewDocumentId = $this->project->documents()->findOrFail($documentId)->id;
        $this->dispatch('open-modal', 'document-preview');
    }

    public function save(DocumentService $service): void
    {
        $this->authorize('create', [Document::class, $this->project]);

        $this->validate([
            'file' => ['required', 'file', 'max:51200'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(DocumentCategory::class)],
            'description' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], attributes: [
            'file' => 'archivo', 'name' => 'nombre', 'category' => 'categoría',
            'description' => 'descripción', 'notes' => 'notas',
        ]);

        $service->create($this->project, new DocumentData(
            name: $this->name,
            category: DocumentCategory::from($this->category),
            description: $this->description !== '' ? $this->description : null,
            notes: $this->notes !== '' ? $this->notes : null,
        ), $this->file, auth()->user());

        $this->dispatch('close-modal', 'document-form');
        $this->dispatch('toast', message: 'Archivo cargado correctamente.');
        $this->reset(['file', 'name', 'description', 'notes']);
    }

    public function saveVersion(DocumentService $service): void
    {
        $document = $this->project->documents()->findOrFail($this->documentId);

        $this->authorize('update', $document);

        $this->validate([
            'file' => ['required', 'file', 'max:51200'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], attributes: ['file' => 'archivo', 'notes' => 'notas']);

        $service->addVersion($document, $this->file, auth()->user(), $this->notes !== '' ? $this->notes : null);

        $this->dispatch('close-modal', 'document-version-form');
        $this->dispatch('toast', message: 'Nueva versión cargada.');
        $this->reset(['file', 'notes', 'documentId']);
    }

    public function deleteDocument(int $documentId, DocumentService $service): void
    {
        $document = $this->project->documents()->findOrFail($documentId);

        $this->authorize('delete', $document);

        $service->delete($document);

        $this->dispatch('toast', message: 'Archivo eliminado.');
    }

    public function render(): View
    {
        $documents = $this->project->documents()
            ->with(['uploader', 'latestVersion'])
            ->withCount('versions')
            ->when($this->search !== '', fn ($query) => $query->search($this->search))
            ->when(DocumentCategory::tryFrom($this->categoryFilter), fn ($query, $category) => $query->where('category', $category->value))
            ->latest()
            ->paginate(10);

        return view('livewire.files.document-library', [
            'documents' => $documents,
            'categories' => DocumentCategory::options(),
            'historyDocument' => $this->historyDocumentId
                ? $this->project->documents()->with(['versions.uploader'])->find($this->historyDocumentId)
                : null,
            'previewDocument' => $this->previewDocumentId
                ? $this->project->documents()->with('latestVersion')->find($this->previewDocumentId)
                : null,
        ]);
    }
}
