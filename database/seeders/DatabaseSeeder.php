<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TrainingEventStatus;
use App\Enums\UserRole;
use App\Models\CourseTemplate;
use App\Models\Product;
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
        $this->seedProducts();
        $this->call(CompetitionEventSeeder::class);
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
                'name' => 'Long Range Prone',
                'slug' => 'long-range-prone',
                'blurb' => 'Prone precision — zero, ballistics and wind out past a kilometre.',
                'icon' => 'heroicon-o-viewfinder-circle',
                'sort_order' => 1,
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
                'name' => 'PRS',
                'slug' => 'prs',
                'blurb' => 'Precision Rifle Series — positional stages against the clock.',
                'icon' => 'heroicon-o-clock',
                'sort_order' => 2,
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
        ];

        $eventOffset = 0;

        foreach ($types as $typeData) {
            $type = TrainingType::updateOrCreate(
                ['slug' => $typeData['slug']],
                [
                    'name' => $typeData['name'],
                    'blurb' => $typeData['blurb'],
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

    protected function seedProducts(): void
    {
        $products = [
            [
                'name' => 'Tune Up Trucker Cap',
                'category' => 'Headwear',
                'description' => '3D puff-embroidered trucker cap in tactical charcoal with a copper reticle.',
                'price_cents' => 32000,
                'stock_qty' => 25,
            ],
            [
                'name' => 'Reticle Morale Patch',
                'category' => 'Patch · Velcro',
                'description' => 'PVC velcro-backed morale patch featuring the Tune Up reticle mark.',
                'price_cents' => 15000,
                'stock_qty' => 40,
            ],
            [
                'name' => 'Weatherproof DOPE Cards',
                'category' => 'Range · Data',
                'description' => 'Set of 5 weatherproof DOPE cards for logging your ballistic solution on the line.',
                'price_cents' => 18000,
                'stock_qty' => 60,
            ],
            [
                // Deliberately out of stock — proves the available() scope hides it.
                'name' => 'Mini IPSC Gong 200mm',
                'category' => 'Steel · 6mm',
                'description' => '200 mm AR500 mini IPSC gong, rated for 6 mm centrefire at distance.',
                'price_cents' => 69000,
                'stock_qty' => 0,
            ],
        ];

        foreach ($products as $data) {
            Product::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'category' => $data['category'],
                    'description' => $data['description'],
                    'price_cents' => $data['price_cents'],
                    'stock_qty' => $data['stock_qty'],
                    'is_active' => true,
                ],
            );
        }
    }
}
