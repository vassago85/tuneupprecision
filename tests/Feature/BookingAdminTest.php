<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\TrainingEventStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Bookings\Pages\ListBookings;
use App\Models\Booking;
use App\Models\CourseTemplate;
use App\Models\TrainingEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BookingAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmed_bookings_for_a_past_event_show_as_completed(): void
    {
        $past = $this->booking($this->event(now()->subWeek()), BookingStatus::Confirmed);
        $future = $this->booking($this->event(now()->addWeek()), BookingStatus::Confirmed);
        $closed = $this->booking($this->event(now()->addDay(), TrainingEventStatus::Completed), BookingStatus::Confirmed);
        $unpaid = $this->booking($this->event(now()->subWeek()), BookingStatus::Pending);
        $running = $this->booking($this->event(now()->subDay(), endsOn: now()->addDay()), BookingStatus::Confirmed);

        $this->assertTrue($past->isCompleted());
        $this->assertTrue($closed->isCompleted());
        $this->assertFalse($future->isCompleted());
        $this->assertFalse($unpaid->isCompleted());
        $this->assertFalse($running->isCompleted());

        $admin = User::factory()->create(['role' => UserRole::Admin]);

        Livewire::actingAs($admin)
            ->test(ListBookings::class)
            ->assertCanSeeTableRecords([$future, $running])
            ->assertCanNotSeeTableRecords([$past, $closed, $unpaid])
            ->set('activeTab', 'completed')
            ->assertCanSeeTableRecords([$past, $closed])
            ->assertCanNotSeeTableRecords([$future, $running, $unpaid])
            ->assertTableColumnFormattedStateSet('status', 'Completed', $past)
            ->set('activeTab', 'all')
            ->assertTableColumnFormattedStateSet('status', 'Confirmed', $future)
            ->assertTableColumnFormattedStateSet('status', 'Pending', $unpaid);

        $this->assertSame(BookingStatus::Confirmed, $past->fresh()->status);
    }

    private function event(
        \DateTimeInterface $startsOn,
        TrainingEventStatus $status = TrainingEventStatus::Published,
        ?\DateTimeInterface $endsOn = null,
    ): TrainingEvent {
        $template = CourseTemplate::firstOrCreate(['slug' => 'zero-to-first-steel'], [
            'title' => 'Zero to First Steel', 'base_price_cents' => 130000,
            'default_capacity' => 6, 'is_active' => true,
        ]);

        return TrainingEvent::create([
            'course_template_id' => $template->id,
            'starts_on' => $startsOn, 'ends_on' => $endsOn, 'venue' => 'Range',
            'capacity' => 6, 'seats_taken' => 1, 'status' => $status,
        ]);
    }

    private function booking(TrainingEvent $event, BookingStatus $status): Booking
    {
        return Booking::create([
            'training_event_id' => $event->id, 'customer_name' => 'Jason', 'email' => 'jason@example.com',
            'seats' => 1, 'amount_cents' => 130000, 'status' => $status,
        ]);
    }
}
