<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TrainingEventStatus;
use App\Enums\UserRole;
use App\Models\CourseTemplate;
use App\Models\TrainingType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAdmin();
        $this->seedCourses();
        $this->call(ProductSeeder::class);
        $this->call(CompetitionEventSeeder::class);
        $this->call(ComponentSeeder::class);
        // Sample testimonials for the homepage carousel — Dirk deletes/edits
        // them from the Filament Testimonials resource. Idempotent.
        $this->call(TestimonialSeeder::class);
    }

    protected function seedAdmin(): void
    {
        $email = (string) env('ADMIN_EMAIL', 'dirk@tuneupprecision.co.za');
        $password = (string) env('ADMIN_PASSWORD', 'password');

        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => (string) env('ADMIN_NAME', 'Dirk'),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'role' => UserRole::Admin,
                'is_verified_member' => true,
            ],
        );

        $this->command?->info('──────────────────────────────────────────────');
        $this->command?->info('  Admin login (Filament /admin):');
        $this->command?->info("    Email:    {$admin->email}");
        $this->command?->info("    Password: {$password}");
        $this->command?->info('──────────────────────────────────────────────');
    }

    protected function seedCourses(): void
    {
        // Admin-managed training disciplines. Each course template belongs to one.
        $types = [
            [
                'name' => 'Precision Long Range',
                'slug' => 'long-range-prone',
                'blurb' => 'Prone precision — zero, ballistics and wind out past a kilometre.',
                'icon' => 'heroicon-o-viewfinder-circle',
                'sort_order' => 1,
                'learnings' => [
                    'Essential firearm safety and range procedures',
                    'Rifle and ammunition fundamentals',
                    'Rifle fit, scope setup and reticle fundamentals',
                    'Setting up and using a ballistic calculator',
                    'Basic external ballistics and bullet trajectory',
                    'Establishing and verifying a ballistic solution',
                    'Correct prone shooting technique and recoil management',
                    'Reading basic wind and mirage conditions',
                    'Understanding elevation and wind corrections',
                    'Understanding and correcting for impacts at distance',
                    'Basic ballistic data and DOPE management',
                    'Safe and responsible long-range shooting practices',
                ],
                'templates' => [
                    [
                        'title' => 'Zero to First Steel',
                        'level' => 'Level 01 · Foundation',
                        'blurb' => 'For new precision shooters. Rifle setup, a true 100 m zero and your first hits on distant steel.',
                        'base_price_cents' => 185000,
                        'specs' => [
                            'Duration' => '1 day · 08:00–16:00',
                            'Prerequisite' => 'None',
                            'Max distance' => '600 m',
                            'Squad' => '6 shooters',
                        ],
                    ],
                    [
                        'title' => 'Applied Long Range',
                        'level' => 'Level 02 · Applied',
                        'blurb' => 'Build and true your ballistic solution, read wind properly, and stretch out past a kilometre with confidence.',
                        'base_price_cents' => 340000,
                        'specs' => [
                            'Duration' => '2 days · weekend',
                            'Prerequisite' => 'Solid zero',
                            'Max distance' => '1200 m',
                            'Squad' => '6 shooters',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'PRS Shooting',
                'slug' => 'prs',
                'blurb' => 'Precision Rifle Series — positional stages against the clock.',
                'icon' => 'heroicon-o-clock',
                'sort_order' => 2,
                'learnings' => [
                    'PRS safety, equipment and match fundamentals',
                    'Reading and understanding a PRS Course of Fire',
                    'Building stable shooting positions from barricades and other props',
                    'Using bags, bipods and positional supports effectively',
                    'Stage planning, target transitions and time management',
                    'Practical techniques for maintaining your position and rifle control under match conditions',
                ],
                'templates' => [
                    [
                        'title' => 'PRS Match Skills',
                        'level' => 'Competition',
                        'blurb' => 'Positional stages under a clock. Barricades, tank traps and transitions — the skills that score on match day.',
                        'base_price_cents' => 420000,
                        'specs' => [
                            'Duration' => '1 day · intensive',
                            'Prerequisite' => 'Solid zero',
                            'Max distance' => '1000 m positional',
                            'Squad' => '6 shooters',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Reloading',
                'slug' => 'reloading',
                'blurb' => 'Handloading for precision — brass prep, load development and truing.',
                'icon' => 'heroicon-o-beaker',
                'sort_order' => 3,
                'learnings' => [
                    'Reloading bench and component safety',
                    'Cartridge components and how they work together',
                    'Safe use of reloading manuals and load data',
                    'Brass inspection, preparation and sizing',
                    'Understanding headspace and case dimensions',
                    'Primer selection and correct primer seating',
                    'Powder measurement and charge consistency',
                    'Bullet seating and cartridge dimensions',
                    'Understanding COAL, CBTO and bullet jump',
                    'Basic quality control and ammunition inspection',
                    'Keeping accurate reloading records and managing load development',
                ],
                'templates' => [
                    [
                        'title' => 'Precision Reloading',
                        'level' => 'Handloading',
                        'blurb' => 'Brass prep, powder and seating-depth development, and how to build a repeatable, accurate load for your rifle.',
                        'base_price_cents' => 265000,
                        'specs' => [
                            'Duration' => '1 day · bench',
                            'Prerequisite' => 'None',
                            'Focus' => 'Load development',
                            'Squad' => '6 shooters',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Handgun Fundamentals',
                'slug' => 'handgun',
                'blurb' => 'Safe, consistent, confident — the fundamentals of handgun shooting.',
                'icon' => 'heroicon-o-shield-check',
                'sort_order' => 4,
                'learnings' => [
                    'Essential firearm safety and range procedures',
                    'Handgun controls, components and safe operation',
                    'Correct stance, grip and sight alignment',
                    'Trigger control and recoil management',
                    'Safe loading, unloading and magazine changes',
                    'Applying shooting fundamentals during practical live-fire exercises',
                    'Basic malfunction recognition and procedures',
                    'Handgun maintenance and equipment fundamentals',
                ],
                'templates' => [
                    [
                        'title' => 'Handgun Fundamentals',
                        'level' => 'Foundation',
                        'blurb' => 'Build the skills, knowledge and confidence to handle and shoot a handgun safely and effectively.',
                        // On-request pricing — no dummy dates on this discipline yet.
                        'base_price_cents' => 0,
                        'specs' => [
                            'Duration' => '1 day · 08:00–16:00',
                            'Prerequisite' => 'None',
                            'Rounds' => 'Approx. 60–80',
                            'Squad' => '6 shooters',
                        ],
                        'skip_events' => true,
                    ],
                ],
            ],
        ];

        $eventOffset = 0;

        foreach ($types as $typeData) {
            $type = TrainingType::updateOrCreate(
                ['slug' => $typeData['slug']],
                [
                    'name' => $typeData['name'],
                    'blurb' => $typeData['blurb'],
                    'learnings' => $typeData['learnings'] ?? [],
                    'icon' => $typeData['icon'],
                    'sort_order' => $typeData['sort_order'],
                    'is_active' => true,
                ],
            );

            foreach ($typeData['templates'] as $data) {
                $template = CourseTemplate::updateOrCreate(
                    ['slug' => Str::slug($data['title'])],
                    [
                        'training_type_id' => $type->id,
                        'title' => $data['title'],
                        'level' => $data['level'],
                        'blurb' => $data['blurb'],
                        'specs' => $data['specs'],
                        'base_price_cents' => $data['base_price_cents'],
                        'default_capacity' => 6,
                        'is_active' => true,
                    ],
                );

                // Some disciplines (e.g. Handgun Fundamentals) are on-request
                // only and should not seed dummy dates — the public card will
                // render its "Dates coming soon" empty state.
                if (! empty($data['skip_events'])) {
                    $eventOffset++;

                    continue;
                }

                // Two published future events per template, staggered so the
                // agenda spreads across months.
                $template->trainingEvents()->updateOrCreate(
                    ['starts_on' => now()->addWeeks(3 + $eventOffset)->toDateString()],
                    [
                        'venue' => 'Private range · Gauteng',
                        'capacity' => 6,
                        'seats_taken' => 2,
                        'status' => TrainingEventStatus::Published,
                    ],
                );

                $template->trainingEvents()->updateOrCreate(
                    ['starts_on' => now()->addWeeks(9 + $eventOffset)->toDateString()],
                    [
                        'venue' => 'Private range · Gauteng',
                        'capacity' => 6,
                        'seats_taken' => 0,
                        'status' => TrainingEventStatus::Published,
                    ],
                );

                // Give the Applied course one deliberately full event to exercise
                // the public "Fully booked" state (which DOES display).
                if ($template->slug === 'applied-long-range') {
                    $template->trainingEvents()->updateOrCreate(
                        ['starts_on' => now()->addWeeks(2)->toDateString()],
                        [
                            'venue' => 'Private range · Gauteng',
                            'capacity' => 6,
                            'seats_taken' => 6,
                            'status' => TrainingEventStatus::Full,
                        ],
                    );
                }

                $eventOffset++;
            }
        }
    }
}
