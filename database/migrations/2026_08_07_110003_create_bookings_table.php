<?php

declare(strict_types=1);

use App\Enums\BookingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('training_event_id')->constrained()->cascadeOnDelete();
            $table->string('customer_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('rifle')->nullable();
            $table->unsignedInteger('seats')->default(1);
            $table->string('reference')->unique();
            $table->unsignedInteger('amount_cents')->default(0);
            $table->string('status')->default(BookingStatus::Pending->value)->index();
            $table->timestamp('hold_expires_at')->nullable();
            $table->timestamps();

            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
