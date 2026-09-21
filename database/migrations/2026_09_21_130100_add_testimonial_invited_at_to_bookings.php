<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            // Set the first time the "please leave a testimonial" email is sent
            // for this booking so the admin invite action can skip already-mailed
            // shooters unless the operator explicitly opts to include them.
            $table->timestamp('testimonial_invited_at')->nullable()->after('hold_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn('testimonial_invited_at');
        });
    }
};
