<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $table->morphs('commentable');
            $table->text('content');
            $table->string('status')->default('open')->index();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('comment_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_attachments');
        Schema::dropIfExists('comments');
    }
};
