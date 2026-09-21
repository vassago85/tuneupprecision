<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\RifleBuilder;
use App\Mail\BuildEnquiry;
use App\Models\Component;
use App\Models\Quote;
use App\Models\RifleBuildShare;
use App\Models\User;
use Database\Seeders\ComponentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class RifleBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ComponentSeeder::class);
        // Rifle builder is currently admin-only (public launch on hold while
        // Dirk finishes the component catalogue), so every route hit needs to
        // be authenticated as the admin.
        $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
    }

    public function test_admin_can_view_builder(): void
    {
        $this->get(route('rifle-builder'))
            ->assertOk()
            ->assertSee('Precision Rifle Builder')
            ->assertSee('Build it.')
            ->assertSee('Request this build');
    }

    public function test_guest_cannot_view_builder(): void
    {
        auth()->logout();

        $this->get(route('rifle-builder'))->assertRedirect(route('login'));
    }

    public function test_non_admin_member_cannot_view_builder(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Member]));

        $this->get(route('rifle-builder'))->assertForbidden();
    }

    public function test_selecting_a_component_updates_totals(): void
    {
        $barrelled = Component::query()->where('slug', 'bergara-b-14-hmr-barrelled-action')->firstOrFail();

        Livewire::test(RifleBuilder::class)
            ->dispatch('rifle-build-changed', selection: [
                'platform' => 'barrelled',
                'singles' => ['barrelled' => $barrelled->id],
                'multis' => [],
                'quantities' => [],
            ])
            ->assertSee('Bergara')
            ->assertSee('Assembly, Torque');
    }

    public function test_incompatible_chassis_shows_disabled_reason(): void
    {
        $tikka = Component::query()->where('slug', 'tikka-t3x-ctr-action')->firstOrFail();
        $manners = Component::query()->where('slug', 'manners-t6a-carbon-stock')->firstOrFail();

        Livewire::test(RifleBuilder::class)
            ->dispatch('rifle-build-changed', selection: [
                'platform' => 'separate',
                'singles' => ['action' => $tikka->id],
                'multis' => [],
                'quantities' => [],
            ])
            ->assertSee('Does not fit Tikka footprint');

        $this->assertNotNull($manners);
    }

    public function test_request_this_build_persists_a_draft_quote_and_mails_both_parties(): void
    {
        Mail::fake();

        $barrelled = Component::query()->where('slug', 'bergara-b-14-hmr-barrelled-action')->firstOrFail();

        Livewire::test(RifleBuilder::class)
            ->dispatch('rifle-build-changed', selection: [
                'platform' => 'barrelled',
                'singles' => ['barrelled' => $barrelled->id],
                'multis' => [],
                'quantities' => [],
            ])
            ->set('customerName', 'Sam Shooter')
            ->set('customerEmail', 'sam@example.com')
            ->set('customerPhone', '0820000000')
            ->set('message', 'Please quote this.')
            ->call('submitRequest')
            ->assertHasNoErrors();

        $quote = Quote::query()->where('customer_email', 'sam@example.com')->first();
        $this->assertNotNull($quote);
        $this->assertSame('draft', $quote->status->value);
        $this->assertNull($quote->created_by);
        $this->assertNotEmpty($quote->lines);
        $this->assertMatchesRegularExpression('/^TU-\d{4}-\d{4}$/', $quote->reference);

        Mail::assertQueued(BuildEnquiry::class, 2);
    }

    public function test_share_code_rehydrates_the_builder(): void
    {
        $barrelled = Component::query()->where('slug', 'bergara-b-14-hmr-barrelled-action')->firstOrFail();

        $share = RifleBuildShare::create([
            'payload' => [
                'platform' => 'barrelled',
                'singles' => ['barrelled' => $barrelled->id],
                'multis' => [],
                'quantities' => [],
            ],
        ]);

        $this->get(route('rifle-builder.share', $share->code))
            ->assertOk();

        Livewire::test(RifleBuilder::class, ['code' => $share->code])
            ->assertSet('shareCode', $share->code)
            ->assertSet('buildSelection.singles.barrelled', $barrelled->id);
    }
}
