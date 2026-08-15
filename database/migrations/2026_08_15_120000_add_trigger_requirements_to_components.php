<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('components', function (Blueprint $table): void {
            $table->boolean('requires_aftermarket_trigger')
                ->default(false)
                ->after('fits_tube_diameters');
            $table->boolean('is_factory_option')
                ->default(false)
                ->after('requires_aftermarket_trigger');
        });
    }

    public function down(): void
    {
        Schema::table('components', function (Blueprint $table): void {
            $table->dropColumn(['requires_aftermarket_trigger', 'is_factory_option']);
        });
    }
};
