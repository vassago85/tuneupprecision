<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Mail\OrderPlaced;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ShopCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_unpublished_stock_stays_off_the_shop_and_cannot_be_bought(): void
    {
        $hidden = Product::query()->where('sku', 'GDONGE2PRO')->firstOrFail();

        $this->get(route('shop'))
            ->assertOk()
            ->assertDontSee($hidden->name);

        $this->get(route('shop.show', $hidden))->assertNotFound();

        $this->post(route('shop.cart.add'), ['product_id' => $hidden->id])
            ->assertRedirect()
            ->assertSessionHas('cart_error');

        $this->get(route('shop.checkout'))->assertRedirect(route('shop'));
    }

    public function test_checkout_places_a_pending_eft_order_and_leaves_stock_alone(): void
    {
        Mail::fake();

        $product = Product::query()->create([
            'name' => 'Range cap',
            'slug' => 'range-cap',
            'description' => 'Charcoal trucker with a copper reticle.',
            'category' => 'Headwear',
            'price_cents' => 32000,
            'stock_qty' => 4,
            'is_active' => true,
        ]);

        $this->get(route('shop'))
            ->assertOk()
            ->assertSee('Range cap')
            ->assertSee('Charcoal trucker with a copper reticle.');

        $same = Product::query()->create([
            'name' => 'Range beanie',
            'slug' => 'range-beanie',
            'category' => 'Headwear',
            'price_cents' => 18000,
            'stock_qty' => 3,
            'is_active' => true,
        ]);
        $other = Product::query()->create([
            'name' => 'Bore snake',
            'slug' => 'bore-snake',
            'category' => 'Cleaning',
            'price_cents' => 9000,
            'stock_qty' => 5,
            'is_active' => true,
        ]);

        $this->get(route('shop.show', $product))
            ->assertOk()
            ->assertSee('Includes 15% VAT')
            ->assertSee('In stock')
            ->assertSee('Delivery inside South Africa')
            ->assertSee('More in Headwear')
            ->assertSee($same->name)
            ->assertDontSee($other->name);

        $this->post(route('shop.cart.add'), [
            'product_id' => $product->id,
            'qty' => 2,
        ])->assertRedirect()->assertSessionHas('cart_open', true);

        $this->get(route('shop.checkout'))
            ->assertOk()
            ->assertSee('Range cap')
            ->assertSee('R640.00')
            ->assertSee('Includes VAT')
            ->assertSee('Included');

        $this->post(route('shop.checkout.place'), $this->customer())
            ->assertRedirect(route('shop.confirmation'));

        $this->get(route('shop.confirmation'))
            ->assertOk()
            ->assertSee('Pay by EFT')
            ->assertSee('Jane Shooter')
            ->assertSee('R640.00');

        $order = Order::query()->with('orderItems', 'payment')->first();
        $this->assertNotNull($order);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(64000, $order->subtotal_cents);
        $this->assertSame(0, $order->shipping_cents);
        $this->assertSame('Hartbeespoort', $order->city);
        $this->assertSame(64000, $order->payment->amount_cents);
        $this->assertSame(PaymentStatus::Pending, $order->payment->status);
        $this->assertSame($order->reference, $order->payment->reference);
        $this->assertCount(1, $order->orderItems);
        $this->assertSame(4, $product->fresh()->stock_qty);
        $this->assertFalse((bool) Product::query()->where('sku', 'KES0857XWLFDEM')->value('is_active'));

        Mail::assertQueued(OrderPlaced::class, 2);
    }

    public function test_a_saved_courier_fee_is_shown_before_payment_and_stored_on_the_order(): void
    {
        Mail::fake();

        Setting::put('shop.shipping_cents', '15000');

        $product = Product::query()->create([
            'name' => 'Range cap',
            'slug' => 'range-cap-courier',
            'category' => 'Headwear',
            'price_cents' => 32000,
            'stock_qty' => 2,
            'is_active' => true,
        ]);

        $this->post(route('shop.cart.add'), [
            'product_id' => $product->id,
            'qty' => 1,
        ])->assertRedirect();

        $this->get(route('shop'))
            ->assertOk()
            ->assertSee('R150.00');

        $this->get(route('shop.checkout'))
            ->assertOk()
            ->assertSee('R150.00')
            ->assertSee('R470.00');

        $this->post(route('shop.checkout.place'), $this->customer())
            ->assertRedirect(route('shop.confirmation'));

        $order = Order::query()->first();
        $this->assertSame(32000, $order->subtotal_cents);
        $this->assertSame(15000, $order->shipping_cents);
        $this->assertSame(47000, $order->payment->amount_cents);
    }

    public function test_a_product_page_shows_every_photo(): void
    {
        $product = Product::query()->create([
            'name' => 'Walker muff',
            'slug' => 'walker-muff',
            'category' => 'Hearing',
            'price_cents' => 309005,
            'stock_qty' => 2,
            'is_active' => true,
        ]);

        $product->addMedia(UploadedFile::fake()->image('front.jpg', 400, 680))
            ->toMediaCollection('images');
        $product->addMedia(UploadedFile::fake()->image('side.jpg', 400, 680))
            ->toMediaCollection('images');

        $response = $this->get(route('shop.show', $product))->assertOk();

        foreach ($product->fresh()->getMedia('images') as $index => $image) {
            $response->assertSee($image->getUrl('web') ?: $image->getUrl(), false);
            $response->assertSee('photo '.($index + 1).' of 2', false);
        }
    }

    /**
     * @return array<string, string>
     */
    private function customer(): array
    {
        return [
            'customer_name' => 'Jane Shooter',
            'email' => 'jane@example.com',
            'phone' => '0820000000',
            'address_line_1' => '1 Range Road',
            'suburb' => 'Hartbeespoort',
            'city' => 'Hartbeespoort',
            'province' => 'North West',
            'postal_code' => '0216',
            'terms' => '1',
        ];
    }
}
