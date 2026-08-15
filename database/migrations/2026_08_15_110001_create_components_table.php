<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('components', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('component_category_id')->constrained()->cascadeOnDelete();
            $table->string('brand');
            $table->string('name');
            $table->string('slug')->unique();
            $table->json('specs')->nullable();
            $table->unsignedInteger('price_cents')->default(0);
            $table->unsignedInteger('cost_cents')->default(0);
            $table->string('image_path')->nullable();
            $table->string('footprint')->nullable();
            $table->json('fits_footprints')->nullable();
            $table->string('tube_diameter')->nullable();
            $table->json('fits_tube_diameters')->nullable();
            $table->unsignedInteger('lead_time_weeks')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_automatic')->default(false);
            $table->boolean('allows_quantity')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('components');
    }
};
