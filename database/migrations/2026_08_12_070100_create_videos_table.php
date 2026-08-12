<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('caption')->nullable();

            // Discipline the video belongs to. Nullable so an "uncategorised"
            // video is still valid (it'll surface under an "Other" tab).
            $table->foreignId('training_type_id')
                ->nullable()
                ->constrained('training_types')
                ->nullOnDelete();

            // Either a YouTube ID (11-char string) OR an uploaded MP4 via
            // Spatie MediaLibrary. If both are set, the uploaded file wins.
            $table->string('youtube_id')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_members_only')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index(['training_type_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
