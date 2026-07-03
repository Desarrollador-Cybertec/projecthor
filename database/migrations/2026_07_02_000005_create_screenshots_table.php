<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screenshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stage_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('activity_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->string('view_name');
            $table->string('module')->nullable();
            $table->string('resolution')->nullable();
            $table->string('platform')->nullable();
            $table->string('image_path');
            $table->string('thumbnail_path')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->string('version')->nullable();
            $table->date('taken_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screenshots');
    }
};
