<?php

declare(strict_types=1);

use App\Enums\TrainingEventStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_template_id')->constrained()->cascadeOnDelete();
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->string('venue');
            $table->unsignedInteger('capacity')->default(6);
            $table->unsignedInteger('seats_taken')->default(0);
            $table->unsignedInteger('price_cents')->nullable();
            $table->string('status')->default(TrainingEventStatus::Draft->value)->index();
            $table->timestamps();

            $table->index('starts_on');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_events');
    }
};
