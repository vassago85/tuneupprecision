<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TestimonialSource;
use App\Models\Testimonial;
use App\Models\TrainingType;
use Illuminate\Database\Seeder;

/**
 * Seeds a small set of approved testimonials so the public carousel on the
 * homepage has something to render out of the box. All entries are `source =
 * manual` and pre-approved, which mirrors how Dirk would enter them from
 * Filament. He can delete or replace any of them from the Testimonials
 * resource; the seeder is idempotent (`firstOrCreate` on the body) so
 * `php artisan db:seed --class=TestimonialSeeder` never duplicates rows.
 */
class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $byType = TrainingType::query()->pluck('id', 'slug');

        /**
         * Each row is [training_type_slug, author, body, weeks_ago].
         * `weeks_ago` becomes both submitted_at and approved_at so the carousel
         * has a sensible newest-first order without everything landing on the
         * same second.
         *
         * @var array<int, array{0: string, 1: string, 2: string, 3: int}>
         */
        $rows = [
            [
                'prs',
                'P. Charsley',
                "An eye opener — thought I knew a lot more than I did. Has really improved my PRS shooting from making better calculations with my Kestrel.",
                2,
            ],
            [
                'long-range-prone',
                'Neil van der Merwe',
                "Rocked up thinking I could shoot. Left with a truing DOPE that actually matches steel at 900. Small squad, one instructor on glass — every miss called.",
                6,
            ],
            [
                'reloading',
                'Jaco Bezuidenhout',
                "Bench day well spent. Powder throw, seating depth, jump — Dirk walked us through the process end-to-end. My groups at 300 haven't looked this tight in years.",
                10,
            ],
            [
                'prs',
                'Marius Steenkamp',
                "Positional work on the barricades is where the wheels came off for me. One day with Dirk and I placed better at the next match than the previous three combined.",
                14,
            ],
        ];

        foreach ($rows as [$slug, $author, $body, $weeksAgo]) {
            $typeId = $byType[$slug] ?? null;

            // If the type isn't seeded yet (e.g. running this seeder in
            // isolation on a fresh DB) skip rather than crash.
            if ($typeId === null) {
                continue;
            }

            Testimonial::firstOrCreate(
                ['body' => $body],
                [
                    'training_type_id' => $typeId,
                    'training_event_id' => null,
                    'author_name' => $author,
                    'is_approved' => true,
                    'source' => TestimonialSource::Manual,
                    'submitted_at' => now()->subWeeks($weeksAgo),
                    'approved_at' => now()->subWeeks($weeksAgo),
                ],
            );
        }
    }
}
