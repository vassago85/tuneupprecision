<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\TestimonialSource;
use App\Enums\TrainingEventStatus;
use App\Filament\Resources\TrainingEvents\Actions\SendTestimonialInvitesAction;
use App\Mail\TestimonialInvitation;
use App\Models\Booking;
use App\Models\CourseTemplate;
use App\Models\Testimonial;
use App\Models\TrainingEvent;
use App\Models\TrainingType;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class TestimonialTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_approved_testimonials_and_hides_pending(): void
    {
        $this->seed(DatabaseSeeder::class);

        $prs = TrainingType::query()->where('slug', 'prs')->firstOrFail();

        Testimonial::factory()->approved()->create([
            'training_type_id' => $prs->id,
            'author_name' => 'Jane Approved',
            'body' => 'A cracker of a training day, walked away with better data.',
        ]);
        Testimonial::factory()->create([
            'training_type_id' => $prs->id,
            'author_name' => 'John Pending',
            'body' => 'Still waiting for review, should not show.',
            'is_approved' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('In their words')
            ->assertSee('Jane Approved')
            ->assertSee('A cracker of a training day')
            ->assertDontSee('John Pending')
            ->assertDontSee('Still waiting for review');
    }

    public function test_home_page_renders_without_the_carousel_when_no_approved_testimonials(): void
    {
        $this->seed(DatabaseSeeder::class);
        // DatabaseSeeder now runs TestimonialSeeder, which drops in a handful
        // of approved sample rows for local dev. Clear them so this test can
        // actually assert the "no testimonials" branch of the home view.
        Testimonial::query()->delete();

        $this->get('/')
            ->assertOk()
            ->assertDontSee('In their words')
            ->assertSee('Meet Dirk');
    }

    public function test_submit_form_requires_a_valid_signature(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->get('/testimonials/submit')
            ->assertForbidden();
    }

    public function test_signed_submit_form_prefills_name_discipline_and_event(): void
    {
        $this->seed(DatabaseSeeder::class);

        $type = TrainingType::query()->where('slug', 'prs')->firstOrFail();
        $event = TrainingEvent::query()->whereHas('courseTemplate', fn ($q) => $q->where('training_type_id', $type->id))->firstOrFail();

        $signed = URL::temporarySignedRoute('testimonials.create', now()->addDays(30), [
            'name' => 'Pat Attendee',
            'training_type_id' => $type->id,
            'training_event_id' => $event->id,
        ]);

        $this->get($signed)
            ->assertOk()
            ->assertSee('Pat Attendee')
            ->assertSee($type->name)
            ->assertSee($event->starts_on->format('D d M Y'));
    }

    public function test_submitting_stores_a_pending_testimonial_from_shooter(): void
    {
        $this->seed(DatabaseSeeder::class);

        $type = TrainingType::query()->where('slug', 'reloading')->firstOrFail();

        $this->post('/testimonials', [
            'author_name' => 'Sam Shooter',
            'training_type_id' => $type->id,
            'body' => 'Learned exactly what I needed at the bench — highly recommend it.',
        ])->assertRedirect(route('testimonials.thanks'));

        $this->assertDatabaseHas('testimonials', [
            'author_name' => 'Sam Shooter',
            'training_type_id' => $type->id,
            'is_approved' => false,
            'source' => TestimonialSource::Shooter->value,
        ]);
    }

    public function test_thanks_page_shows_whatsapp_share(): void
    {
        $this->get('/testimonials/thanks')
            ->assertOk()
            ->assertSee('Share on WhatsApp')
            ->assertSee('wa.me/', false);
    }

    public function test_invite_action_queues_mail_for_confirmed_attendees_and_stamps_bookings(): void
    {
        Mail::fake();
        $this->seed(DatabaseSeeder::class);

        [$event, $confirmed, $pending, $noEmail] = $this->seedInvitableEvent();

        $queued = SendTestimonialInvitesAction::dispatch($event, includeAlreadyInvited: false);

        $this->assertSame(1, $queued);
        Mail::assertQueued(TestimonialInvitation::class, 1);
        Mail::assertQueued(TestimonialInvitation::class, fn (TestimonialInvitation $mail): bool => $mail->booking->is($confirmed));

        $this->assertNotNull($confirmed->fresh()->testimonial_invited_at);
        $this->assertNull($pending->fresh()->testimonial_invited_at);
        $this->assertNull($noEmail->fresh()->testimonial_invited_at);
    }

    public function test_reinvite_is_a_noop_unless_the_include_already_invited_flag_is_set(): void
    {
        Mail::fake();
        $this->seed(DatabaseSeeder::class);

        [$event, $confirmed] = $this->seedInvitableEvent();
        $confirmed->forceFill(['testimonial_invited_at' => now()->subDay()])->save();

        $this->assertSame(0, SendTestimonialInvitesAction::dispatch($event, includeAlreadyInvited: false));
        Mail::assertNothingQueued();

        $this->assertSame(1, SendTestimonialInvitesAction::dispatch($event, includeAlreadyInvited: true));
        Mail::assertQueued(TestimonialInvitation::class, 1);
    }

    /**
     * Build a past training event with three bookings: one confirmed (should
     * be invited), one pending (skipped), one confirmed but with no email
     * (skipped).
     *
     * @return array{0: TrainingEvent, 1: Booking, 2: Booking, 3: Booking}
     */
    private function seedInvitableEvent(): array
    {
        $template = CourseTemplate::query()->where('slug', 'prs-match-skills')->firstOrFail();

        $event = TrainingEvent::create([
            'course_template_id' => $template->id,
            'starts_on' => now()->subWeek()->toDateString(),
            'ends_on' => now()->subWeek()->toDateString(),
            'venue' => 'Private range · Gauteng',
            'capacity' => 6,
            'seats_taken' => 3,
            'status' => TrainingEventStatus::Completed,
        ]);

        $confirmed = Booking::create([
            'training_event_id' => $event->id,
            'customer_name' => 'Alex Confirmed',
            'email' => 'alex@example.com',
            'seats' => 1,
            'amount_cents' => 420000,
            'status' => BookingStatus::Confirmed,
        ]);
        $pending = Booking::create([
            'training_event_id' => $event->id,
            'customer_name' => 'Pat Pending',
            'email' => 'pat@example.com',
            'seats' => 1,
            'amount_cents' => 420000,
            'status' => BookingStatus::Pending,
        ]);
        $noEmail = Booking::create([
            'training_event_id' => $event->id,
            'customer_name' => 'Sam NoEmail',
            'email' => '',
            'seats' => 1,
            'amount_cents' => 420000,
            'status' => BookingStatus::Confirmed,
        ]);

        return [$event, $confirmed, $pending, $noEmail];
    }
}
