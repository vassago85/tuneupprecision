<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('component_id')->nullable()->constrained()->nullOnDelete();
            $table->string('group_label');
            $table->string('brand');
            $table->string('description');
            $table->json('specs')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('unit_price_cents')->default(0);
            $table->unsignedInteger('line_total_cents')->default(0);
            $table->unsignedInteger('unit_cost_cents')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_lines');
    }
};
