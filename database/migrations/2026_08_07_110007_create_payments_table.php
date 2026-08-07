<?php

declare(strict_types=1);

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->morphs('payable');
            $table->string('method')->default(PaymentMethod::Eft->value);
            $table->string('reference')->nullable();
            $table->unsignedInteger('amount_cents')->default(0);
            $table->string('status')->default(PaymentStatus::Pending->value)->index();
            $table->timestamp('paid_at')->nullable();
            $table->string('proof_path')->nullable();
            $table->string('gateway_ref')->nullable();
            $table->json('gateway_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
