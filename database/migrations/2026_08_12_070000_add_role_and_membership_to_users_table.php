<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Distinguishes Dirk (admin) from public members. Filament panel
            // access is now gated on this.
            $table->string('role')->default('member')->after('password');

            // Toggled by Dirk from /admin — controls access to gated
            // ("members-only") videos on The Range.
            $table->boolean('is_verified_member')->default(false)->after('role');
        });

        // Only Dirk exists in production so far; promote every pre-existing user
        // to admin + verified so the panel keeps working after this migration.
        DB::table('users')->update([
            'role' => 'admin',
            'is_verified_member' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['role', 'is_verified_member']);
        });
    }
};
