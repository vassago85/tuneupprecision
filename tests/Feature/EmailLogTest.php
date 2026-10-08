<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\EmailLogs\EmailLogResource;
use App\Filament\Resources\EmailLogs\Pages\ListEmailLogs;
use App\Filament\Widgets\NeedsActionWidget;
use App\Mail\ContactEnquiry;
use App\Models\EmailLog;
use App\Models\Setting;
use App\Models\User;
use App\Support\MailSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Tests\TestCase;

class EmailLogTest extends TestCase
{
    use RefreshDatabase;

    private const array ENQUIRY = [
        'name' => 'Alex',
        'email' => 'alex@example.com',
        'phone' => null,
        'subject' => 'Rifle build',
        'message' => 'Hi Dirk',
    ];

    public function test_a_queued_email_that_goes_out_is_logged_as_sent(): void
    {
        Mail::to('dirk@example.com')->queue(new ContactEnquiry(self::ENQUIRY));

        $log = EmailLog::query()->sole();

        $this->assertSame(EmailLog::SENT, $log->status);
        $this->assertSame('dirk@example.com', $log->recipients);
        $this->assertSame(ContactEnquiry::class, $log->mailable);
        $this->assertSame('Contact enquiry', $log->typeLabel());
        $this->assertStringContainsString('Rifle build', (string) $log->subject);
        $this->assertSame(config('mail.default'), $log->mailer);
        $this->assertNotEmpty($log->message_id);
    }

    public function test_a_queued_email_that_runs_out_of_retries_is_logged_as_failed(): void
    {
        Mail::extend('broken', fn () => new class extends AbstractTransport
        {
            protected function doSend(SentMessage $message): void
            {
                throw new TransportException('Mailgun said: Forbidden');
            }

            public function __toString(): string
            {
                return 'broken';
            }
        });
        config(['mail.mailers.broken' => ['transport' => 'broken']]);
        Setting::put('mail.mailer', 'broken');
        MailSettings::apply();

        try {
            Mail::to('dirk@example.com')->queue(new ContactEnquiry(self::ENQUIRY));
            $this->fail('The broken transport should have thrown.');
        } catch (TransportException) {
        }

        $log = EmailLog::query()->sole();

        $this->assertSame(EmailLog::FAILED, $log->status);
        $this->assertSame('dirk@example.com', $log->recipients);
        $this->assertSame(ContactEnquiry::class, $log->mailable);
        $this->assertStringContainsString('Rifle build', (string) $log->subject);
        $this->assertSame('Mailgun said: Forbidden', $log->error);
    }

    public function test_admin_sees_the_log_and_a_badge_for_recent_failures(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
        EmailLog::query()->create([
            'status' => EmailLog::SENT,
            'mailable' => ContactEnquiry::class,
            'mailer' => 'mailgun',
            'recipients' => 'dirk@example.com',
            'subject' => 'New course booking · TU-1234',
        ]);
        EmailLog::query()->create([
            'status' => EmailLog::FAILED,
            'mailer' => 'mailgun',
            'recipients' => 'alex@example.com',
            'subject' => 'Seat held · TU-1234',
            'error' => 'Domain not found',
        ]);

        $this->get(EmailLogResource::getUrl('index'))->assertOk();

        Livewire::test(ListEmailLogs::class)
            ->assertSee('New course booking · TU-1234')
            ->assertSee('Seat held · TU-1234')
            ->assertSee('Domain not found')
            ->assertSee('Failed');

        $this->assertSame('1', EmailLogResource::getNavigationBadge());
    }

    public function test_log_mailer_rows_are_flagged_as_not_delivered(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
        EmailLog::query()->create([
            'status' => EmailLog::SENT,
            'mailer' => 'log',
            'recipients' => 'dirk@example.com',
            'subject' => 'Hello',
        ]);

        Livewire::test(ListEmailLogs::class)->assertSee('Log only');
    }

    public function test_old_entries_are_pruned(): void
    {
        $old = EmailLog::query()->create(['status' => EmailLog::SENT, 'recipients' => 'a@example.com']);
        $old->forceFill(['created_at' => now()->subDays(EmailLog::KEEP_DAYS + 1)])->save();
        EmailLog::query()->create(['status' => EmailLog::SENT, 'recipients' => 'b@example.com']);

        $this->artisan('model:prune', ['--model' => [EmailLog::class]])->assertSuccessful();

        $this->assertSame(['b@example.com'], EmailLog::query()->pluck('recipients')->all());
    }

    public function test_dashboard_flags_failed_emails_and_a_stalled_queue(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
        EmailLog::query()->create([
            'status' => EmailLog::FAILED,
            'recipients' => 'dirk@example.com',
            'error' => 'Forbidden',
        ]);
        config(['queue.default' => 'database']);
        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => '{}',
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => now()->subHour()->getTimestamp(),
            'created_at' => now()->subHour()->getTimestamp(),
        ]);

        Livewire::test(NeedsActionWidget::class)
            ->assertSee('Email failed to send')
            ->assertSee('Email stuck in the queue')
            ->assertSee('The mail worker is not running');
    }

    public function test_dashboard_ignores_a_queue_that_is_keeping_up(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
        config(['queue.default' => 'database']);
        DB::table('jobs')->insert([
            'queue' => 'default',
            'payload' => '{}',
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => now()->subSeconds(20)->getTimestamp(),
            'created_at' => now()->subSeconds(20)->getTimestamp(),
        ]);

        Livewire::test(NeedsActionWidget::class)
            ->assertDontSee('stuck in the queue')
            ->assertSee('All caught up');
    }
}
