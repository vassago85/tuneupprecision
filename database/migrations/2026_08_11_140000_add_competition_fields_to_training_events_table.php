<?php

declare(strict_types=1);

use App\Enums\EventKind;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_events', function (Blueprint $table): void {
            // What kind of event this is. Existing rows are training instances.
            $table->string('kind')->default(EventKind::Training->value)->index()->after('id');

            // Competition-only fields (null for training events).
            $table->string('title')->nullable()->after('kind');
            $table->foreignId('training_type_id')->nullable()->after('title')->constrained()->nullOnDelete();
            $table->string('dirk_role')->nullable()->after('training_type_id');
            $table->string('external_url')->nullable()->after('dirk_role');
            $table->unsignedInteger('entry_fee_cents')->nullable()->after('external_url');

            // Competitions have no course template, so it must be optional.
            $table->foreignId('course_template_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('training_events', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('training_type_id');
            $table->dropColumn(['kind', 'title', 'dirk_role', 'external_url', 'entry_fee_cents']);
            // Note: course_template_id is left nullable on rollback to avoid
            // failing when competition rows (null course) still exist.
        });
    }
};
