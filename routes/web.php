<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CommentAttachmentDownloadController;
use App\Http\Controllers\DocumentDownloadController;
use App\Http\Controllers\EvidenceDownloadController;
use App\Http\Controllers\ProjectExportController;
use App\Livewire;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', Livewire\Dashboard\Index::class)->name('dashboard');

    // Exportaciones (antes de las rutas con {project} para evitar colisiones de slug).
    Route::get('/exports/projects.xlsx', [ProjectExportController::class, 'projectsXlsx'])->name('projects.export.xlsx');

    Route::get('/projects', Livewire\Projects\Index::class)->name('projects.index');
    Route::get('/projects/create', Livewire\Projects\CreateProject::class)->name('projects.create');
    Route::get('/projects/{project}', Livewire\Projects\Show::class)->name('projects.show');
    Route::get('/projects/{project}/edit', Livewire\Projects\EditProject::class)->name('projects.edit');
    Route::get('/projects/{project}/export/report.pdf', [ProjectExportController::class, 'reportPdf'])->name('projects.export.pdf');
    Route::get('/projects/{project}/export/activities.xlsx', [ProjectExportController::class, 'activitiesXlsx'])->name('projects.activities.export.xlsx');

    // Descargas
    Route::get('/evidences/{evidence}/download', EvidenceDownloadController::class)->name('evidences.download');
    Route::get('/documents/{document}/download', [DocumentDownloadController::class, 'latest'])->name('documents.download');
    Route::get('/documents/{document}/versions/{version}/download', [DocumentDownloadController::class, 'version'])->name('documents.versions.download');
    Route::get('/comments/attachments/{attachment}/download', CommentAttachmentDownloadController::class)->name('comments.attachments.download');

    Route::get('/notifications', Livewire\Notifications\Index::class)->name('notifications.index');
    Route::get('/profile', Livewire\Profile\Edit::class)->name('profile.edit');

    Route::get('/users', Livewire\Users\Index::class)
        ->middleware('admin')
        ->name('users.index');
});
