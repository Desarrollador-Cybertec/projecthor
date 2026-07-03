<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('client_name');
            $table->string('logo_path')->nullable();
            $table->string('color', 9)->default('#6366f1');
            $table->text('description')->nullable();
            $table->foreignId('responsible_id')->constrained('users')->restrictOnDelete();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->string('priority')->default('medium')->index();
            $table->string('status')->default('active')->index();
            $table->string('production_url')->nullable();
            $table->string('staging_url')->nullable();
            $table->string('documentation_url')->nullable();
            $table->string('repository_url')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('project_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['project_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_user');
        Schema::dropIfExists('projects');
    }
};
