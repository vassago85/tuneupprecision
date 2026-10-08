<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\ManageMailSettings;
use App\Models\Setting;
use App\Models\User;
use App\Support\OwnerInbox;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MailSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_alerts_go_to_the_business_inbox_by_default(): void
    {
        $this->assertSame('info@tuneupprecision.co.za', OwnerInbox::email());
    }

    public function test_saved_notification_inbox_wins(): void
    {
        Setting::put(OwnerInbox::SETTING_KEY, ' Dirk@Example.com ');

        $this->assertSame('dirk@example.com', OwnerInbox::email());
    }

    public function test_an_invalid_saved_inbox_falls_back_to_the_default(): void
    {
        Setting::put(OwnerInbox::SETTING_KEY, 'not-an-email');

        $this->assertSame(OwnerInbox::DEFAULT_EMAIL, OwnerInbox::email());
    }

    public function test_admin_can_set_where_notifications_go(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ManageMailSettings::class)
            ->assertSchemaStateSet(['notify_email' => OwnerInbox::DEFAULT_EMAIL])
            ->fillForm([
                'mailer' => 'log',
                'notify_email' => 'alerts@example.com',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('alerts@example.com', OwnerInbox::email());
    }

    public function test_send_test_email_saves_the_form_then_sends(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ManageMailSettings::class)
            ->fillForm([
                'mailer' => 'log',
                'notify_email' => 'alerts@example.com',
            ])
            ->callAction('sendTest', data: ['recipient' => 'someone@example.com'])
            ->assertHasNoActionErrors()
            ->assertNotified('Test email sent');

        $this->assertSame('alerts@example.com', OwnerInbox::email());
        $this->assertSame('log', Setting::get('mail.mailer'));
    }
}
