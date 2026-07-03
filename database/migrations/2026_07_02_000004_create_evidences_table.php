<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->index();
            $table->string('version')->default('1.0');
            $table->string('file_path')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('url')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidences');
    }
};
