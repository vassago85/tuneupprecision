<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\ContactEnquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_privacy_and_terms_pages_render(): void
    {
        $this->get('/contact')->assertOk()->assertSee('Message Dirk through the site');
        $this->get('/privacy')->assertOk()->assertSee('Privacy Policy')->assertSee('POPIA');
        $this->get('/terms')->assertOk()->assertSee('Terms of use')->assertSee('Contact only through this site');
    }

    public function test_contact_form_prefills_subject_and_sends_mail(): void
    {
        Mail::fake();

        $this->get('/contact?subject='.rawurlencode('Book: PRS 12 Sep'))
            ->assertOk()
            ->assertSee('Book: PRS 12 Sep');

        $this->post('/contact', [
            'name' => 'Pat Tester',
            'email' => 'pat@example.com',
            'phone' => '0820000000',
            'subject' => 'Book: PRS 12 Sep',
            'message' => 'Please hold a seat.',
            'ts' => now()->subSeconds(10)->timestamp,
        ])->assertRedirect(route('contact.create'));

        Mail::assertQueued(ContactEnquiry::class, 2);
    }

    public function test_honeypot_and_fast_submit_pretend_success_without_mail(): void
    {
        Mail::fake();

        $this->post('/contact', [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'subject' => 'Spam',
            'message' => 'Buy now',
            'company' => 'Acme SEO',
            'ts' => now()->subSeconds(10)->timestamp,
        ])->assertRedirect(route('contact.create'));

        $this->post('/contact', [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'subject' => 'Spam',
            'message' => 'Buy now',
            'ts' => now()->timestamp,
        ])->assertRedirect(route('contact.create'));

        Mail::assertNothingQueued();
    }
}
