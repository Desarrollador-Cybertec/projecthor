<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->morphs('auditable');
            $table->string('event');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('created_at');

            $table->index(['auditable_type', 'auditable_id', 'created_at'], 'audits_subject_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
