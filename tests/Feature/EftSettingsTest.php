<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\ManageEftSettings;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EftSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_eft_settings_which_persist(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(ManageEftSettings::class)
            ->fillForm([
                'bank_name' => 'Capitec',
                'account_name' => 'Tune Up Test',
                'account_number' => '1234567890',
                'branch_code' => '470010',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Capitec', Setting::get('eft.bank_name'));
        $this->assertSame('1234567890', Setting::get('eft.account_number'));
    }

    public function test_saved_values_take_precedence_over_env_defaults(): void
    {
        Setting::put('eft.bank_name', 'Nedbank');

        $details = \App\Support\Eft::details();

        $this->assertSame('Nedbank', $details['bank_name']);
        // Unset key falls back to the config/env default.
        $this->assertSame(config('tuneup.eft.branch_code'), $details['branch_code']);
    }
}
