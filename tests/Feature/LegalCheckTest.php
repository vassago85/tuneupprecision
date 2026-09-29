<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\LegalCompliance;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LegalCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_legal_check_fails_when_a_key_is_blank(): void
    {
        $this->artisan('legal:check')
            ->expectsOutputToContain('legal_name')
            ->assertFailed();
    }

    public function test_legal_check_fails_when_a_key_matches_the_placeholder_pattern(): void
    {
        $this->fillLegalConfig();

        config(['legal.legal_phone' => '+27 00 000 0000']);

        $this->artisan('legal:check')
            ->expectsOutputToContain('legal_phone')
            ->assertFailed();
    }

    public function test_legal_check_passes_when_all_keys_are_populated(): void
    {
        $this->fillLegalConfig();

        $this->artisan('legal:check')
            ->expectsOutputToContain('passed')
            ->assertSuccessful();
    }

    public function test_legal_check_uses_filament_settings_overrides(): void
    {
        $this->fillLegalConfig(['legal.legal_phone' => null]);

        Setting::put('business.tel', '+27 12 345 6789');

        $this->artisan('legal:check')->assertSuccessful();
    }

    public function test_admin_can_save_the_legal_identity_on_the_compliance_page(): void
    {
        $admin = User::factory()->create();
        $identity = [
            'legal_name' => 'Tune Up Precision (Pty) Ltd',
            'trading_as' => 'Tune Up Precision',
            'legal_status' => 'Private company',
            'registration_no' => '2026/123456/07',
            'vat_no' => '4123456789',
            'dealer_licence_no' => 'DL-1234567',
            'office_bearers' => 'Dirk Pio',
            'physical_address' => '1 Example Road, Pretoria, Gauteng',
            'postal_address' => 'PO Box 12, Pretoria, 0182',
            'legal_email' => 'info@tuneupprecision.co.za',
            'legal_phone' => '+27 12 345 6789',
        ];

        Livewire::actingAs($admin)
            ->test(LegalCompliance::class)
            ->fillForm($identity)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Tune Up Precision (Pty) Ltd', Setting::get('legal.legal_name'));
        $this->assertSame('+27 12 345 6789', Setting::get('business.tel'));
        $this->artisan('legal:check')->assertSuccessful();

        $this->get(route('legal.privacy'))
            ->assertOk()
            ->assertSee('Tune Up Precision (Pty) Ltd')
            ->assertSee('1 Example Road, Pretoria, Gauteng');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function fillLegalConfig(array $overrides = []): void
    {
        config(array_merge([
            'legal.legal_name' => 'Tune Up Precision (Pty) Ltd',
            'legal.trading_as' => 'Tune Up Precision',
            'legal.legal_status' => 'Private company',
            'legal.registration_no' => '2026/123456/07',
            'legal.vat_no' => '4123456789',
            'legal.dealer_licence_no' => 'DL-1234567',
            'legal.office_bearers' => 'Dirk Pio',
            'legal.physical_address' => '1 Example Road, Pretoria, Gauteng',
            'legal.postal_address' => 'PO Box 1, Pretoria, 0001',
            'legal.legal_email' => 'info@tuneupprecision.co.za',
            'legal.legal_phone' => '+27 12 345 6789',
        ], $overrides));
    }
}
