<?php

declare(strict_types=1);

use App\Enums\ComponentSelectionMode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('component_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('hint')->nullable();
            $table->string('selection_mode')->default(ComponentSelectionMode::Single->value);
            $table->boolean('is_optional')->default(false);
            $table->boolean('allows_quantity')->default(false);
            $table->boolean('is_hidden')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('component_categories');
    }
};
