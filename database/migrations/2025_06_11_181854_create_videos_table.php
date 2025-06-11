<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('original_filename');
            $table->string('s3_path');
            $table->string('s3_key');
            $table->string('mime_type');
            $table->bigInteger('file_size');
            $table->string('resolution')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->string('duration_formatted')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
