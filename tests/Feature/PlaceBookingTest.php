<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\MarkPaid;
use App\Actions\PlaceBooking;
use App\Enums\BookingStatus;
use App\Enums\EventKind;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\TrainingEventStatus;
use App\Models\Booking;
use App\Models\CourseTemplate;
use App\Models\TrainingEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PlaceBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_placing_a_booking_holds_seats_until_the_configured_deadline(): void
    {
        config(['tuneup.booking_hold_hours' => 48]);
        $event = $this->event(['capacity' => 6, 'seats_taken' => 1, 'price_cents' => 50000]);

        $booking = app(PlaceBooking::class)->place($event, $this->details(['seats' => 2]));

        $event->refresh();
        $this->assertSame(3, (int) $event->seats_taken);
        $this->assertSame(TrainingEventStatus::Published, $event->status);
        $this->assertSame(100000, $booking->amount_cents);
        $this->assertSame(BookingStatus::Pending, $booking->status);
        $this->assertTrue($booking->hold_expires_at->between(now()->addHours(47), now()->addHours(49)));
    }

    public function test_the_last_seats_mark_the_date_full(): void
    {
        $event = $this->event(['capacity' => 2, 'seats_taken' => 0]);

        app(PlaceBooking::class)->place($event, $this->details(['seats' => 2]));

        $event->refresh();
        $this->assertSame(2, (int) $event->seats_taken);
        $this->assertSame(TrainingEventStatus::Full, $event->status);
        $this->assertFalse(app(PlaceBooking::class)->isBookable($event));
    }

    public function test_an_overbook_does_not_change_the_date(): void
    {
        $event = $this->event(['capacity' => 2, 'seats_taken' => 1]);

        try {
            app(PlaceBooking::class)->place($event, $this->details(['seats' => 2]));
            $this->fail('Expected an overbook to be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('seats', $exception->errors());
        }

        $event->refresh();
        $this->assertSame(1, (int) $event->seats_taken);
        $this->assertSame(TrainingEventStatus::Published, $event->status);
        $this->assertSame(0, Booking::query()->count());
    }

    public function test_closed_dates_cannot_be_placed(): void
    {
        $cases = [
            $this->event(['status' => TrainingEventStatus::Draft]),
            $this->event(['status' => TrainingEventStatus::Cancelled]),
            $this->event(['status' => TrainingEventStatus::Full, 'capacity' => 1, 'seats_taken' => 1]),
            $this->event(['starts_on' => now()->subDay()->toDateString()]),
            TrainingEvent::query()->create([
                'kind' => EventKind::Competition,
                'title' => 'Match',
                'starts_on' => now()->addWeek()->toDateString(),
                'venue' => 'Range',
                'capacity' => 10,
                'status' => TrainingEventStatus::Published,
            ]),
        ];

        foreach ($cases as $event) {
            $this->assertFalse(app(PlaceBooking::class)->isBookable($event));

            try {
                app(PlaceBooking::class)->place($event, $this->details());
                $this->fail('Expected '.$event->id.' to be rejected.');
            } catch (ValidationException) {
                // Closed dates must not create a booking.
            }
        }

        $this->assertSame(0, Booking::query()->count());
    }

    public function test_confirming_payment_does_not_take_the_seat_again(): void
    {
        Mail::fake();
        $event = $this->event(['capacity' => 4, 'seats_taken' => 0, 'price_cents' => 185000]);
        $booking = app(PlaceBooking::class)->place($event, $this->details());

        $payment = $booking->payment()->create([
            'method' => PaymentMethod::Eft,
            'amount_cents' => $booking->amount_cents,
            'status' => PaymentStatus::Pending,
        ]);

        app(MarkPaid::class)->handle($payment);

        $event->refresh();
        $booking->refresh();
        $this->assertSame(1, (int) $event->seats_taken);
        $this->assertSame(BookingStatus::Confirmed, $booking->status);
        $this->assertNull($booking->hold_expires_at);
        $this->assertSame(PaymentStatus::Paid, $payment->fresh()->status);
    }

    public function test_expired_holds_free_the_seat_and_reopen_a_full_date(): void
    {
        $event = $this->event(['capacity' => 1, 'seats_taken' => 0]);
        $booking = app(PlaceBooking::class)->place($event, $this->details());
        $this->assertSame(TrainingEventStatus::Full, $event->fresh()->status);

        $kept = Booking::query()->create([
            'training_event_id' => $event->id,
            'customer_name' => 'Paid',
            'email' => 'paid@example.com',
            'seats' => 1,
            'amount_cents' => 100,
            'status' => BookingStatus::Confirmed,
            'hold_expires_at' => now()->subHour(),
        ]);

        $this->travel(73)->hours();

        $this->artisan('bookings:release-holds')->assertSuccessful();

        $this->travelBack();

        $event->refresh();
        $booking->refresh();
        $this->assertSame(BookingStatus::Cancelled, $booking->status);
        $this->assertSame(0, (int) $event->seats_taken);
        $this->assertSame(TrainingEventStatus::Published, $event->status);
        $this->assertSame(BookingStatus::Confirmed, $kept->fresh()->status);
    }

    public function test_a_hold_that_has_not_expired_is_left_alone(): void
    {
        $event = $this->event();
        $booking = app(PlaceBooking::class)->place($event, $this->details());

        $this->artisan('bookings:release-holds')
            ->expectsOutput('No expired holds to release.')
            ->assertSuccessful();

        $this->assertSame(BookingStatus::Pending, $booking->fresh()->status);
        $this->assertSame(1, (int) $event->fresh()->seats_taken);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function event(array $overrides = []): TrainingEvent
    {
        $template = CourseTemplate::query()->create([
            'title' => 'Applied Long Range',
            'slug' => 'applied-'.uniqid(),
            'base_price_cents' => 340000,
            'default_capacity' => 6,
            'is_active' => true,
        ]);

        return TrainingEvent::query()->create(array_merge([
            'course_template_id' => $template->id,
            'starts_on' => now()->addWeeks(3)->toDateString(),
            'venue' => 'Range',
            'capacity' => 6,
            'seats_taken' => 0,
            'status' => TrainingEventStatus::Published,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array{customer_name: string, email: string, phone: string, rifle: ?string, seats: int}
     */
    private function details(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Sam Shooter',
            'email' => 'sam@example.com',
            'phone' => '0820000000',
            'rifle' => null,
            'seats' => 1,
        ], $overrides);
    }
}
