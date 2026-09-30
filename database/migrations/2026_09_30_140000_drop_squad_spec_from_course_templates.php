<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Squad size is derived from max participants. A stored "Squad" row
        // can disagree with the seat count (for example "4 shooters" next
        // to "6 of 6 seats left").
        foreach (DB::table('course_templates')->get(['id', 'specs']) as $row) {
            $specs = json_decode((string) $row->specs, true);

            if (! is_array($specs)) {
                continue;
            }

            $changed = false;

            foreach (array_keys($specs) as $label) {
                if (strcasecmp((string) $label, 'Squad') === 0) {
                    unset($specs[$label]);
                    $changed = true;
                }
            }

            if ($changed) {
                DB::table('course_templates')->where('id', $row->id)->update([
                    'specs' => json_encode($specs),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // The typed squad strings are not recoverable.
    }
};
