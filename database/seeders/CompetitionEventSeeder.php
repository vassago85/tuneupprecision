<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EventKind;
use App\Enums\TrainingEventStatus;
use App\Models\TrainingEvent;
use App\Models\TrainingType;
use Illuminate\Database\Seeder;

class CompetitionEventSeeder extends Seeder
{
    /**
     * Seed a handful of realistic competition/guest events Dirk is attending.
     *
     * Competitions differ from training dates: no CourseTemplate, guests join
     * via a free RSVP (see event_rsvps), and Dirk's role varies (matchDirector,
     * competing, guestCoach, ...). Keyed idempotently on kind + title +
     * starts_on so re-runs update in place instead of duplicating.
     */
    public function run(): void
    {
        $prs = TrainingType::query()->where('slug', 'prs')->first();
        $prone = TrainingType::query()->where('slug', 'long-range-prone')->first();

        $events = [
            [
                'title' => 'Royal Flush Steel Challenge',
                'training_type_id' => $prs?->id,
                'dirk_role' => 'Match Director',
                'external_url' => 'https://royalflushsteel.co.za',
                'entry_fee_cents' => 65000,
                'starts_on' => now()->addWeeks(5)->next(\Carbon\CarbonInterface::SATURDAY)->toDateString(),
                'ends_on' => now()->addWeeks(5)->next(\Carbon\CarbonInterface::SATURDAY)->addDay()->toDateString(),
                'venue' => 'Bela-Bela · Limpopo',
                'capacity' => 40,
            ],
            [
                'title' => 'SAPRF National Championships',
                'training_type_id' => $prs?->id,
                'dirk_role' => 'Competing · SAPRF board',
                'external_url' => 'https://saprf.co.za',
                'entry_fee_cents' => 95000,
                'starts_on' => now()->addWeeks(10)->next(\Carbon\CarbonInterface::FRIDAY)->toDateString(),
                'ends_on' => now()->addWeeks(10)->next(\Carbon\CarbonInterface::FRIDAY)->addDays(2)->toDateString(),
                'venue' => 'De Brug Range · Bloemfontein',
                'capacity' => 60,
            ],
            [
                'title' => 'Pretoria Precision Rifle Club Monthly Match',
                'training_type_id' => $prs?->id,
                'dirk_role' => 'Match Director',
                'external_url' => null,
                'entry_fee_cents' => 25000,
                'starts_on' => now()->addWeeks(2)->next(\Carbon\CarbonInterface::SUNDAY)->toDateString(),
                'ends_on' => null,
                'venue' => 'PPRC · Gauteng',
                'capacity' => 30,
            ],
            [
                'title' => 'Long Range Prone Open',
                'training_type_id' => $prone?->id,
                'dirk_role' => 'Competing',
                'external_url' => null,
                'entry_fee_cents' => 70000,
                'starts_on' => now()->addWeeks(7)->next(\Carbon\CarbonInterface::SATURDAY)->toDateString(),
                'ends_on' => now()->addWeeks(7)->next(\Carbon\CarbonInterface::SATURDAY)->addDay()->toDateString(),
                'venue' => 'Vaal Range · Free State',
                'capacity' => 35,
            ],
            [
                'title' => 'ELR Invitational',
                'training_type_id' => null,
                'dirk_role' => 'Guest coach',
                'external_url' => null,
                'entry_fee_cents' => 90000,
                'starts_on' => now()->addWeeks(13)->next(\Carbon\CarbonInterface::SATURDAY)->toDateString(),
                'ends_on' => now()->addWeeks(13)->next(\Carbon\CarbonInterface::SATURDAY)->addDay()->toDateString(),
                'venue' => 'Boskop · North West',
                'capacity' => 20,
            ],
        ];

        foreach ($events as $data) {
            TrainingEvent::updateOrCreate(
                [
                    'kind' => EventKind::Competition->value,
                    'title' => $data['title'],
                    'starts_on' => $data['starts_on'],
                ],
                [
                    'course_template_id' => null,
                    'training_type_id' => $data['training_type_id'],
                    'dirk_role' => $data['dirk_role'],
                    'external_url' => $data['external_url'],
                    'entry_fee_cents' => $data['entry_fee_cents'],
                    'ends_on' => $data['ends_on'],
                    'venue' => $data['venue'],
                    'capacity' => $data['capacity'],
                    'seats_taken' => 0,
                    'status' => TrainingEventStatus::Published,
                ],
            );
        }
    }
}
