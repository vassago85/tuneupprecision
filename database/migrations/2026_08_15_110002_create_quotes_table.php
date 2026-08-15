<?php

declare(strict_types=1);

use App\Enums\QuoteStatus;
use App\Enums\RiflePlatform;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table): void {
            $table->id();
            $table->string('reference')->unique();
            $table->string('status')->default(QuoteStatus::Draft->value)->index();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('licence_status')->nullable();
            $table->string('platform')->default(RiflePlatform::Barrelled->value);
            $table->string('chambering')->nullable();
            $table->string('barrel_length')->nullable();
            $table->string('barrel_twist')->nullable();
            $table->string('barrel_finish')->nullable();
            $table->unsignedInteger('subtotal_cents')->default(0);
            $table->unsignedInteger('discount_amount_cents')->default(0);
            $table->unsignedInteger('total_cents')->default(0);
            $table->unsignedInteger('vat_amount_cents')->default(0);
            $table->unsignedInteger('deposit_percent')->default(50);
            $table->string('lead_time')->nullable();
            $table->text('notes')->nullable();
            $table->date('valid_until')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('customer_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
