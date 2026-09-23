<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_consignment_lines_import_with_quantity_and_nett_ex_vat(): void
    {
        $lines = require database_path('data/consignment_ioc49297.php');

        $this->assertCount(22, $lines);
        $this->assertSame(22, Product::query()->whereIn('sku', array_column($lines, 'sku'))->count());

        $extended = Product::query()
            ->whereIn('sku', array_column($lines, 'sku'))
            ->get()
            ->sum(fn (Product $product): int => $product->cost_cents * $product->stock_qty);

        // Invoice IOC49297 amount excl. tax.
        $this->assertSame(10_307_000, $extended);

        $adapter = Product::query()->where('sku', 'SP2042R')->first();
        $this->assertNotNull($adapter);
        $this->assertSame(1, $adapter->stock_qty);
        $this->assertSame(179_500, $adapter->cost_cents);
        $this->assertNull($adapter->selling_ex_vat_cents);
        $this->assertFalse($adapter->is_active);
        $this->assertSame(0, $adapter->price_cents);
    }

    public function test_selling_price_ex_vat_stores_the_inclusive_shop_price(): void
    {
        $product = Product::query()->where('sku', 'OILST30FP/5P')->firstOrFail();

        $product->selling_ex_vat = '70';
        $product->round_price_up = false;
        $product->save();

        $product->refresh();
        $this->assertSame(7000, $product->selling_ex_vat_cents);
        $this->assertSame(8050, $product->price_cents);

        $product->round_price_up = true;
        $product->save();

        $this->assertSame(8100, $product->fresh()->price_cents);
    }

    public function test_a_direct_shop_price_is_left_alone_until_a_selling_price_is_set(): void
    {
        $product = Product::create([
            'name' => 'Tune Up Trucker Cap',
            'slug' => 'tune-up-trucker-cap',
            'category' => 'Headwear',
            'price_cents' => 32000,
            'stock_qty' => 4,
            'is_active' => true,
        ]);

        $product->name = 'Tune Up Trucker Cap';
        $product->save();

        $this->assertSame(32000, $product->fresh()->price_cents);
    }

    public function test_renaming_a_product_updates_its_slug_and_a_blank_description_is_cleared(): void
    {
        $product = Product::query()->where('sku', 'GDONGE2PRO')->firstOrFail();

        $product->name = 'Bluetooth ear muffs';
        $product->description = 'Over-ear muffs with Bluetooth.';
        $product->save();

        $product->refresh();
        $this->assertSame('bluetooth-ear-muffs', $product->slug);
        $this->assertSame('Over-ear muffs with Bluetooth.', $product->description);
        $this->assertFalse($product->is_active);

        $product->description = '   ';
        $product->save();

        $this->assertNull($product->fresh()->description);
    }

    public function test_a_short_description_shows_on_the_shop_card(): void
    {
        Product::create([
            'name' => 'Range cap',
            'slug' => 'range-cap',
            'description' => 'Charcoal trucker with a copper reticle.',
            'price_cents' => 32000,
            'stock_qty' => 2,
            'is_active' => true,
        ]);

        $this->get('/shop')
            ->assertOk()
            ->assertSee('Range cap')
            ->assertSee('Charcoal trucker with a copper reticle.')
            ->assertDontSee('Bluetooth ear muffs');
    }

    public function test_admin_product_form_exposes_selling_price_and_rounding(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $product = Product::query()->where('sku', 'KES0857XWLFDEM')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/products/'.$product->id.'/edit')
            ->assertOk()
            ->assertSee('Selling price ex VAT')
            ->assertSee('Round up to the next rand')
            ->assertSee('Nett cost ex VAT')
            ->assertSee('Shop name')
            ->assertSee('Short description');
    }

    public function test_unpriced_stock_stays_off_the_shop(): void
    {
        $this->get('/shop')
            ->assertOk()
            ->assertDontSee('Kestrel 5700X WEZ weather meter');
    }
}
