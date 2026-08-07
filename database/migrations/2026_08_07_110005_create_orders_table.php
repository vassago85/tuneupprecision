<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('reference')->unique();
            $table->string('customer_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->unsignedInteger('subtotal_cents')->default(0);
            $table->string('status')->default(OrderStatus::Pending->value)->index();
            $table->timestamps();

            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
