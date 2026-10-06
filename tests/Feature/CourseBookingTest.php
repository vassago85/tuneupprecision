<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\EventKind;
use App\Enums\TrainingEventStatus;
use App\Mail\BookingPlaced;
use App\Models\Booking;
use App\Models\CourseTemplate;
use App\Models\TrainingEvent;
use App\Models\TrainingType;
use App\Support\Eft;
use App\Support\OwnerInbox;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CourseBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_pages_and_calendar_link_open_dates_to_the_booking_form(): void
    {
        $event = $this->event();
        $course = $event->courseTemplate;

        $this->get('/courses')
            ->assertOk()
            ->assertSee('/book/'.$event->id, false)
            ->assertSee('>Book<', false)
            ->assertDontSee('contact#Book', false);

        $this->get(route('courses.show', $course))
            ->assertOk()
            ->assertSee('/book/'.$event->id, false)
            ->assertSee($course->title);

        $calendar = $this->get('/calendar?month='.$event->starts_on->format('Y-m'))->assertOk();
        $this->assertMatchesRegularExpression('/book\\\\+\\/'.$event->id.'/', $calendar->getContent());
    }

    public function test_squad_size_follows_max_participants_not_a_typed_spec(): void
    {
        $event = $this->event(['capacity' => 6, 'seats_taken' => 0]);
        $event->courseTemplate->update([
            'specs' => [
                'Duration' => '1 day · bench',
                'Squad' => '4 shooters',
            ],
            'default_capacity' => 4,
        ]);

        $this->get(route('courses.show', $event->courseTemplate))
            ->assertOk()
            ->assertSee('6 shooters')
            ->assertSee('6 of 6 seats left')
            ->assertDontSee('4 shooters');
    }

    public function test_squad_size_uses_course_max_participants_when_there_are_no_dates(): void
    {
        $type = TrainingType::query()->firstOrCreate(
            ['slug' => 'handgun'],
            ['name' => 'Handgun Fundamentals', 'is_active' => true, 'sort_order' => 4],
        );

        $template = CourseTemplate::query()->updateOrCreate(['slug' => 'handgun-fundamentals'], [
            'training_type_id' => $type->id,
            'title' => 'Handgun Fundamentals',
            'level' => 'Foundation',
            'blurb' => 'Safe handling.',
            'specs' => [
                'Duration' => '1 day',
                'Squad' => '4 shooters',
            ],
            'base_price_cents' => 0,
            'default_capacity' => 8,
            'is_active' => true,
        ]);

        $this->get(route('courses.show', $template))
            ->assertOk()
            ->assertSee('8 shooters')
            ->assertDontSee('4 shooters');
    }

    public function test_booking_form_shows_the_date_price_and_seats_left(): void
    {
        $event = $this->event(['capacity' => 6, 'seats_taken' => 2, 'price_cents' => 185000]);

        $this->get(route('bookings.create', $event))
            ->assertOk()
            ->assertSee('Book '.$event->courseTemplate->title)
            ->assertSee('Reserve this seat')
            ->assertSee('4 of 6 seats left')
            ->assertSee('R1 850')
            ->assertSee('72 hours')
            ->assertSee('name="seats"', false)
            ->assertDontSee('Send message');
    }

    public function test_submitting_the_form_reserves_the_seat_and_shows_payment_details(): void
    {
        Mail::fake();
        $event = $this->event(['price_cents' => 185000, 'capacity' => 6, 'seats_taken' => 1]);

        $this->post(route('bookings.store', $event), $this->payload())
            ->assertRedirect(route('bookings.confirmed'));

        $event->refresh();
        $this->assertSame(2, (int) $event->seats_taken);
        $this->assertSame(TrainingEventStatus::Published, $event->status);

        $booking = Booking::query()->where('email', 'sam@example.com')->firstOrFail();
        $this->assertSame(BookingStatus::Pending, $booking->status);
        $this->assertSame(1, $booking->seats);
        $this->assertSame(185000, $booking->amount_cents);
        $this->assertSame('6.5 Creedmoor', $booking->rifle);
        $this->assertNotNull($booking->hold_expires_at);
        $this->assertTrue($booking->hold_expires_at->between(now()->addHours(71), now()->addHours(73)));
        $this->assertMatchesRegularExpression('/^TU-B-\d{6}$/', $booking->reference);

        $eft = Eft::details();
        $this->get(route('bookings.confirmed'))
            ->assertOk()
            ->assertSee('Seat held')
            ->assertSee('Sam Shooter')
            ->assertSee($booking->reference)
            ->assertSee($eft['bank_name'])
            ->assertSee($eft['account_number'])
            ->assertSee('To pay');

        Mail::assertQueued(BookingPlaced::class, function (BookingPlaced $mail): bool {
            return $mail->forCustomer && $mail->hasTo('sam@example.com');
        });
        Mail::assertQueued(BookingPlaced::class, function (BookingPlaced $mail): bool {
            return ! $mail->forCustomer && $mail->hasTo(OwnerInbox::email());
        });

        $customerHtml = (new BookingPlaced($booking, true))->render();
        $this->assertStringContainsString($booking->reference, $customerHtml);
        $this->assertStringContainsString('Your seat is held', $customerHtml);

        $ownerHtml = (new BookingPlaced($booking, false))->render();
        $this->assertStringContainsString('New course booking', $ownerHtml);
        $this->assertStringContainsString('0820000000', $ownerHtml);
    }

    public function test_booking_several_seats_charges_per_shooter_and_can_fill_the_date(): void
    {
        Mail::fake();
        $event = $this->event(['capacity' => 3, 'seats_taken' => 1, 'price_cents' => 10000]);

        $this->post(route('bookings.store', $event), $this->payload(['seats' => 2]))
            ->assertRedirect(route('bookings.confirmed'));

        $event->refresh();
        $this->assertSame(3, (int) $event->seats_taken);
        $this->assertSame(TrainingEventStatus::Full, $event->status);
        $this->assertSame(20000, Booking::query()->firstOrFail()->amount_cents);

        $this->get('/courses')->assertDontSee('/book/'.$event->id, false);
        $this->get(route('bookings.create', $event))->assertNotFound();
    }

    public function test_a_zero_price_date_is_held_without_asking_for_payment(): void
    {
        Mail::fake();
        $event = $this->event(['price_cents' => 0]);
        $event->courseTemplate->update(['base_price_cents' => 0]);

        $this->post(route('bookings.store', $event), $this->payload())
            ->assertRedirect(route('bookings.confirmed'));

        $this->assertSame(0, Booking::query()->firstOrFail()->amount_cents);
        $this->get(route('bookings.confirmed'))
            ->assertOk()
            ->assertSee('Dirk will confirm the amount')
            ->assertDontSee('To pay');
    }

    public function test_the_form_rejects_incomplete_or_oversized_requests(): void
    {
        $event = $this->event(['capacity' => 2, 'seats_taken' => 0]);

        $this->post(route('bookings.store', $event), ['ts' => now()->timestamp - 10])
            ->assertSessionHasErrors(['customer_name', 'email', 'phone', 'seats', 'terms']);
        $this->assertSame(0, Booking::query()->count());

        $this->from(route('bookings.create', $event))
            ->followingRedirects()
            ->post(route('bookings.store', $event), $this->payload([
                'email' => 'not-an-email',
                'seats' => 9,
                'terms' => null,
            ]))
            ->assertOk()
            ->assertSee('Reserve this seat')
            ->assertSee($event->courseTemplate->title);

        $event->refresh();
        $this->assertSame(0, (int) $event->seats_taken);
        $this->assertSame(0, Booking::query()->count());
    }

    public function test_bots_do_not_take_a_seat(): void
    {
        Mail::fake();
        $event = $this->event();

        $this->post(route('bookings.store', $event), $this->payload(['company' => 'Acme']))
            ->assertRedirect(route('courses'));

        $this->post(route('bookings.store', $event), $this->payload(['ts' => now()->timestamp]))
            ->assertRedirect(route('courses'));

        $this->assertSame(0, (int) $event->fresh()->seats_taken);
        $this->assertSame(0, Booking::query()->count());
        Mail::assertNothingQueued();
    }

    public function test_closed_dates_are_not_bookable(): void
    {
        $full = $this->event(['capacity' => 2, 'seats_taken' => 2, 'status' => TrainingEventStatus::Full]);
        $past = $this->event(['starts_on' => now()->subDay()->toDateString()]);
        $draft = $this->event(['status' => TrainingEventStatus::Draft]);
        $cancelled = $this->event(['status' => TrainingEventStatus::Cancelled]);
        $match = TrainingEvent::query()->create([
            'kind' => EventKind::Competition,
            'title' => 'Provincial',
            'starts_on' => now()->addWeek()->toDateString(),
            'venue' => 'Range',
            'capacity' => 20,
            'seats_taken' => 0,
            'status' => TrainingEventStatus::Published,
        ]);

        foreach ([$full, $past, $draft, $cancelled, $match] as $event) {
            $this->get(route('bookings.create', $event))->assertNotFound();
            $this->post(route('bookings.store', $event), $this->payload())->assertNotFound();
        }

        $this->assertSame(0, Booking::query()->count());
    }

    public function test_dates_on_a_switched_off_course_are_hidden_and_not_bookable(): void
    {
        $event = $this->event();
        $event->courseTemplate->update(['is_active' => false]);

        $this->get('/courses')
            ->assertOk()
            ->assertDontSee('/book/'.$event->id, false)
            ->assertDontSee(route('courses.show', $event->courseTemplate), false);

        $this->get('/calendar?month='.$event->starts_on->format('Y-m'))
            ->assertOk()
            ->assertDontSee('Zero to First Steel');

        $this->get(route('bookings.create', $event))->assertNotFound();
        $this->post(route('bookings.store', $event), $this->payload())->assertNotFound();
        $this->assertSame(0, Booking::query()->count());
    }

    public function test_confirmation_without_a_booking_returns_to_courses(): void
    {
        $this->get(route('bookings.confirmed'))->assertRedirect(route('courses'));
    }

    public function test_a_past_calendar_date_does_not_offer_booking(): void
    {
        $event = $this->event(['starts_on' => now()->subDays(3)->toDateString()]);

        $calendar = $this->get('/calendar?month='.$event->starts_on->format('Y-m'))->assertOk();
        $this->assertDoesNotMatchRegularExpression('/book\\\\+\\/'.$event->id.'/', $calendar->getContent());
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function event(array $overrides = []): TrainingEvent
    {
        $type = TrainingType::query()->firstOrCreate(
            ['slug' => 'long-range'],
            ['name' => 'Precision Long Range', 'is_active' => true, 'sort_order' => 1],
        );

        $template = CourseTemplate::query()->create([
            'training_type_id' => $type->id,
            'title' => 'Zero to First Steel',
            'slug' => 'zero-to-first-steel-'.$type->courseTemplates()->count(),
            'level' => 'Foundation',
            'blurb' => 'A first day on the line.',
            'base_price_cents' => 185000,
            'default_capacity' => 6,
            'is_active' => true,
        ]);

        return TrainingEvent::query()->create(array_merge([
            'course_template_id' => $template->id,
            'starts_on' => now()->addDay()->toDateString(),
            'venue' => 'Private range · Gauteng',
            'capacity' => 6,
            'seats_taken' => 0,
            'status' => TrainingEventStatus::Published,
        ], $overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Sam Shooter',
            'email' => 'sam@example.com',
            'phone' => '0820000000',
            'rifle' => '6.5 Creedmoor',
            'seats' => 1,
            'terms' => '1',
            'ts' => now()->timestamp - 10,
        ], $overrides);
    }
}
