<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('level')->nullable();
            $table->text('blurb')->nullable();
            $table->json('specs')->nullable();
            $table->unsignedInteger('base_price_cents')->default(0);
            $table->unsignedInteger('default_capacity')->default(6);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_templates');
    }
};
