<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Pages\ManageEftSettings;
use App\Models\Setting;
use App\Models\User;
use App\Support\Eft;
use App\Support\ShopShipping;
use App\Support\SocialLinks;
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

        $details = Eft::details();

        $this->assertSame('Nedbank', $details['bank_name']);
        // Unset key falls back to the config/env default.
        $this->assertSame(config('tuneup.eft.branch_code'), $details['branch_code']);
    }

    public function test_admin_can_save_social_links_and_the_footer_uses_them(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(ManageEftSettings::class)
            ->fillForm([
                'instagram' => 'https://instagram.com/tuneupprecision',
                'facebook' => 'https://facebook.com/tuneupprecision',
                'whatsapp' => 'https://wa.me/27821234567',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('https://instagram.com/tuneupprecision', Setting::get('social.instagram'));
        $this->assertNull(Setting::get('social.youtube'));

        $this->get('/')
            ->assertOk()
            ->assertSee('https://instagram.com/tuneupprecision', false)
            ->assertSee('https://facebook.com/tuneupprecision', false)
            ->assertSee('https://wa.me/27821234567', false)
            ->assertDontSee('aria-label="YouTube"', false);

        $this->assertSame([
            'instagram',
            'facebook',
            'whatsapp',
        ], array_column(SocialLinks::visible(), 'key'));
    }

    public function test_admin_can_set_the_shop_courier_fee(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(ManageEftSettings::class)
            ->fillForm([
                'shipping_rands' => 150,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('15000', Setting::get('shop.shipping_cents'));
        $this->assertSame(15000, ShopShipping::cents());

        Livewire::actingAs($admin)
            ->test(ManageEftSettings::class)
            ->fillForm([
                'shipping_rands' => 0,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(0, ShopShipping::cents());
    }

    public function test_social_link_rejects_a_value_that_is_not_a_url(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)
            ->test(ManageEftSettings::class)
            ->fillForm([
                'instagram' => 'not-a-url',
            ])
            ->call('save')
            ->assertHasFormErrors(['instagram']);
    }
}
