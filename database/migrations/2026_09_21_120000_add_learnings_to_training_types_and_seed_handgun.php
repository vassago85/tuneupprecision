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
        Schema::table('training_types', function (Blueprint $table): void {
            $table->json('learnings')->nullable()->after('blurb');
        });

        // Seed the "What you'll learn" bullet lists for the existing three
        // disciplines and add the fourth (Handgun Fundamentals) so production
        // does not require a full reseed.
        $lists = self::learningLists();
        $now = now();

        foreach ($lists as $slug => $data) {
            $payload = [
                'learnings' => json_encode($data['learnings']),
                'updated_at' => $now,
            ];

            $existing = DB::table('training_types')->where('slug', $slug)->first();

            if ($existing !== null) {
                DB::table('training_types')->where('id', $existing->id)->update($payload);

                continue;
            }

            // Insert the missing type (Handgun Fundamentals on existing
            // databases). The seeder handles a fresh install; this covers the
            // production migration path.
            DB::table('training_types')->insert([
                'name' => $data['name'],
                'slug' => $slug,
                'blurb' => $data['blurb'],
                'learnings' => json_encode($data['learnings']),
                'icon' => $data['icon'],
                'sort_order' => $data['sort_order'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Add a course template for Handgun Fundamentals if the type exists but
        // the template does not (fresh installs get this from the seeder).
        $handgunType = DB::table('training_types')->where('slug', 'handgun')->first();

        if ($handgunType !== null) {
            $templateSlug = 'handgun-fundamentals';
            $templateExists = DB::table('course_templates')->where('slug', $templateSlug)->exists();

            if (! $templateExists) {
                DB::table('course_templates')->insert([
                    'training_type_id' => $handgunType->id,
                    'title' => 'Handgun Fundamentals',
                    'slug' => $templateSlug,
                    'level' => 'Foundation',
                    'blurb' => 'Build the skills, knowledge and confidence to handle and shoot a handgun safely and effectively.',
                    'specs' => json_encode([
                        'Duration' => '1 day · 08:00–16:00',
                        'Prerequisite' => 'None',
                        'Rounds' => 'Approx. 60–80',
                        'Squad' => '6 shooters',
                    ]),
                    'base_price_cents' => 0,
                    'default_capacity' => 6,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('training_types', function (Blueprint $table): void {
            $table->dropColumn('learnings');
        });
    }

    /**
     * The "What you'll learn" lists sourced from Dirk's OneNote notes, keyed
     * by training-type slug. Includes metadata for inserting the Handgun type
     * on existing databases.
     *
     * @return array<string, array{name: string, blurb: string, icon: string, sort_order: int, learnings: array<int, string>}>
     */
    protected static function learningLists(): array
    {
        return [
            'long-range-prone' => [
                'name' => 'Precision Long Range',
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
            ],
            'prs' => [
                'name' => 'PRS Shooting',
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
            ],
            'reloading' => [
                'name' => 'Reloading',
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
            ],
            'handgun' => [
                'name' => 'Handgun Fundamentals',
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
            ],
        ];
    }
};
