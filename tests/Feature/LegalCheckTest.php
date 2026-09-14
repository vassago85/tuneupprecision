<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
