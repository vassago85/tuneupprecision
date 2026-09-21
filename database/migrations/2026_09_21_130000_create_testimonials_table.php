<?php

declare(strict_types=1);

use App\Enums\TestimonialSource;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('training_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('training_event_id')->nullable()->constrained()->nullOnDelete();
            $table->string('author_name');
            $table->text('body');
            $table->boolean('is_approved')->default(false)->index();
            $table->string('source')->default(TestimonialSource::Manual->value)->index();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
