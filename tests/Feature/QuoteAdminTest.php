<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\AcceptQuote;
use App\Actions\IssueQuote;
use App\Actions\MarkPaid;
use App\Enums\PaymentStatus;
use App\Enums\QuoteStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Quotes\Pages\CreateQuote;
use App\Filament\Resources\Quotes\Pages\EditQuote;
use App\Models\Component;
use App\Models\Quote;
use App\Models\User;
use App\RifleBuilder\BuildSelection;
use App\Services\RifleBuildService;
use Database\Seeders\ComponentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuoteAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ComponentSeeder::class);
    }

    public function test_filament_can_create_a_quote(): void
    {
        $admin = $this->admin();
        $barrelled = Component::query()->where('slug', 'bergara-b-14-hmr-barrelled-action')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(CreateQuote::class)
            ->set('buildSelection', [
                'platform' => 'barrelled',
                'singles' => ['barrelled' => $barrelled->id],
                'multis' => [],
                'quantities' => [],
            ])
            ->fillForm([
                'customer_name' => 'Dirk Client',
                'customer_email' => 'client@example.com',
                'platform' => 'barrelled',
                'deposit_percent' => 50,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('quotes', [
            'customer_email' => 'client@example.com',
            'status' => QuoteStatus::Draft->value,
        ]);
    }

    public function test_pdf_download_returns_a_pdf(): void
    {
        $quote = $this->makeQuote();
        $output = app(IssueQuote::class)->pdf($quote)->output();

        $this->assertStringStartsWith('%PDF', $output);
    }

    public function test_accepting_a_quote_creates_a_pending_deposit_payment(): void
    {
        $quote = $this->makeQuote();

        app(AcceptQuote::class)->handle($quote);

        $quote->refresh();
        $this->assertSame(QuoteStatus::Accepted, $quote->status);
        $this->assertNotNull($quote->payment);
        $this->assertSame(PaymentStatus::Pending, $quote->payment->status);
        $this->assertSame($quote->depositCents(), $quote->payment->amount_cents);
        $this->assertSame($quote->reference, $quote->payment->reference);
    }

    public function test_mark_paid_converts_an_accepted_quote(): void
    {
        $quote = $this->makeQuote();
        app(AcceptQuote::class)->handle($quote);

        app(MarkPaid::class)->handle($quote->payment()->first());

        $this->assertSame(QuoteStatus::Converted, $quote->fresh()->status);
        $this->assertSame(PaymentStatus::Paid, $quote->payment()->first()->status);
    }

    public function test_edit_quote_page_renders_for_an_admin(): void
    {
        $quote = $this->makeQuote();

        Livewire::actingAs($this->admin())
            ->test(EditQuote::class, ['record' => $quote->getRouteKey()])
            ->assertOk()
            ->assertSee($quote->reference);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => UserRole::Admin,
            'is_verified_member' => true,
        ]);
    }

    private function makeQuote(): Quote
    {
        $barrelled = Component::query()->where('slug', 'bergara-b-14-hmr-barrelled-action')->firstOrFail();

        return app(RifleBuildService::class)->persistQuote(
            new BuildSelection(
                singles: ['barrelled' => $barrelled->id],
            ),
            [
                'customer_name' => 'Pat',
                'customer_email' => 'pat@example.com',
            ],
        );
    }
}
