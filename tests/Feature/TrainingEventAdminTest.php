<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\EventKind;
use App\Enums\TrainingEventStatus;
use App\Enums\UserRole;
use App\Filament\Resources\TrainingEvents\Pages\EditTrainingEvent;
use App\Filament\Resources\TrainingEvents\TrainingEventResource;
use App\Filament\Widgets\UpcomingTrainingWidget;
use App\Models\Booking;
use App\Models\CourseTemplate;
use App\Models\TrainingEvent;
use App\Models\TrainingType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TrainingEventAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_a_course_date(): void
    {
        $this->actingAs($this->admin());
        $event = $this->courseDate(now()->addWeeks(3)->toDateString());
        $newDate = now()->addWeeks(6)->toDateString();

        Livewire::test(EditTrainingEvent::class, ['record' => $event->getRouteKey()])
            ->fillForm(['starts_on' => $newDate, 'venue' => 'New range'])
            ->call('save')
            ->assertHasNoFormErrors();

        $event->refresh();
        $this->assertSame($newDate, $event->starts_on->toDateString());
        $this->assertSame('New range', $event->venue);
    }

    public function test_dashboard_lists_only_course_dates_and_links_to_edit(): void
    {
        $this->actingAs($this->admin());
        $listed = $this->courseDate(now()->addWeeks(2)->toDateString());
        $this->courseDate(now()->addWeeks(3)->toDateString(), TrainingEventStatus::Draft);
        $this->competition();

        Livewire::test(UpcomingTrainingWidget::class)
            ->assertCanSeeTableRecords([$listed])
            ->assertCountTableRecords(1)
            ->assertSee(TrainingEventResource::getUrl('edit', ['record' => $listed]), false);
    }

    public function test_prune_is_a_dry_run_without_force(): void
    {
        $this->competition();

        $this->artisan('events:prune')->assertSuccessful();

        $this->assertSame(1, TrainingEvent::query()->count());
    }

    public function test_prune_keeps_course_dates_and_events_with_bookings(): void
    {
        $listed = $this->courseDate(now()->addWeeks(2)->toDateString());
        $draft = $this->courseDate(now()->addWeeks(3)->toDateString(), TrainingEventStatus::Draft);
        $past = $this->courseDate(now()->subWeeks(2)->toDateString(), TrainingEventStatus::Completed);
        $competition = $this->competition();

        $booked = $this->courseDate(now()->subWeek()->toDateString(), TrainingEventStatus::Completed);
        Booking::create([
            'training_event_id' => $booked->id,
            'customer_name' => 'Alex',
            'email' => 'alex@example.com',
            'seats' => 1,
            'amount_cents' => 185000,
            'status' => BookingStatus::Confirmed,
        ]);

        $this->artisan('events:prune', ['--force' => true])->assertSuccessful();

        $this->assertModelExists($listed);
        $this->assertModelExists($booked);
        $this->assertModelMissing($draft);
        $this->assertModelMissing($past);
        $this->assertModelMissing($competition);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => UserRole::Admin]);
    }

    private function courseDate(string $startsOn, TrainingEventStatus $status = TrainingEventStatus::Published): TrainingEvent
    {
        $template = CourseTemplate::query()->firstOrCreate(
            ['slug' => 'zero-to-first-steel'],
            [
                'training_type_id' => TrainingType::factory()->create()->id,
                'title' => 'Zero to First Steel',
                'base_price_cents' => 185000,
                'default_capacity' => 6,
                'is_active' => true,
            ],
        );

        return $template->trainingEvents()->create([
            'kind' => EventKind::Training,
            'starts_on' => $startsOn,
            'venue' => 'Private range · Gauteng',
            'capacity' => 6,
            'seats_taken' => 0,
            'status' => $status,
        ]);
    }

    private function competition(): TrainingEvent
    {
        return TrainingEvent::create([
            'kind' => EventKind::Competition,
            'title' => 'Royal Flush Steel Challenge',
            'starts_on' => now()->addWeeks(4)->toDateString(),
            'venue' => 'Bela-Bela',
            'capacity' => 40,
            'seats_taken' => 0,
            'status' => TrainingEventStatus::Published,
        ]);
    }
}
