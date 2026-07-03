<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('objective')->nullable();
            $table->string('status')->default('pending')->index();
            $table->unsignedTinyInteger('progress')->default(0);
            $table->date('starts_on')->nullable();
            $table->date('estimated_end_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stages');
    }
};
