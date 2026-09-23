<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\CancelOrder;
use App\Actions\FulfillOrder;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Widgets\ShopDeskWidget;
use App\Mail\OrderDispatched;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class ShopAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_marking_an_order_sent_emails_the_customer_once(): void
    {
        Mail::fake();

        $order = $this->paidOrder();

        app(FulfillOrder::class)->handle($order);
        app(FulfillOrder::class)->handle($order->fresh());

        $this->assertSame(OrderStatus::Fulfilled, $order->fresh()->status);
        Mail::assertQueued(OrderDispatched::class, 1);
    }

    public function test_cancelling_a_pending_order_leaves_stock_alone(): void
    {
        $product = Product::create([
            'name' => 'Cap', 'slug' => 'cap', 'category' => 'Headwear',
            'price_cents' => 32000, 'stock_qty' => 4, 'is_active' => true,
        ]);
        $order = Order::create([
            'customer_name' => 'Jane', 'email' => 'jane@example.com',
            'subtotal_cents' => 32000, 'status' => OrderStatus::Pending,
        ]);
        $order->orderItems()->create([
            'product_id' => $product->id,
            'name_snapshot' => 'Cap',
            'price_cents_snapshot' => 32000,
            'qty' => 1,
        ]);

        app(CancelOrder::class)->handle($order);

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(4, $product->fresh()->stock_qty);
    }

    public function test_cancelling_a_paid_order_puts_the_stock_back(): void
    {
        $product = Product::create([
            'name' => 'Cap', 'slug' => 'cap', 'category' => 'Headwear',
            'price_cents' => 32000, 'stock_qty' => 4, 'is_active' => true,
        ]);
        $order = $this->paidOrder($product);
        $product->decrement('stock_qty', 3);

        app(CancelOrder::class)->handle($order);

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(4, $product->fresh()->stock_qty);
    }

    public function test_a_sent_order_cannot_be_cancelled(): void
    {
        $product = Product::create([
            'name' => 'Cap', 'slug' => 'cap-sent', 'category' => 'Headwear',
            'price_cents' => 32000, 'stock_qty' => 4, 'is_active' => true,
        ]);
        $order = $this->paidOrder($product);
        $order->update(['status' => OrderStatus::Fulfilled]);

        app(CancelOrder::class)->handle($order);

        $this->assertSame(OrderStatus::Fulfilled, $order->fresh()->status);
        $this->assertSame(4, $product->fresh()->stock_qty);
    }

    public function test_the_order_desk_shows_the_queue_and_the_invoice_breakdown(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $order = $this->paidOrder();

        Livewire::actingAs($admin)
            ->test(ListOrders::class)
            ->assertSee('To send')
            ->assertSee($order->reference)
            ->set('activeTab', 'to-pay')
            ->assertDontSee($order->reference);

        $this->actingAs($admin)
            ->get('/admin/orders/'.$order->id)
            ->assertOk()
            ->assertSee($order->reference)
            ->assertSee('Jane')
            ->assertSee('Test Cap')
            ->assertSee('1 Range Road')
            ->assertSee('R960.00')
            ->assertSee('R125.22')
            ->assertSee('R834.78')
            ->assertSee('Download invoice')
            ->assertSee('Mark as sent');

        Livewire::actingAs($admin)
            ->test(ViewOrder::class, ['record' => $order->getKey()])
            ->callAction('fulfill');

        $this->assertSame(OrderStatus::Fulfilled, $order->fresh()->status);
    }

    public function test_the_catalogue_can_be_split_between_the_shop_and_unpublished_stock(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        Product::create([
            'name' => 'Shop Cap', 'slug' => 'shop-cap', 'category' => 'Headwear',
            'price_cents' => 32000, 'stock_qty' => 2, 'is_active' => true,
        ]);
        Product::create([
            'name' => 'Consignment Muff', 'slug' => 'consignment-muff', 'category' => 'Hearing',
            'price_cents' => 0, 'stock_qty' => 1, 'is_active' => false,
        ]);

        Livewire::actingAs($admin)
            ->test(ListProducts::class)
            ->set('activeTab', 'shop')
            ->assertSee('Shop Cap')
            ->assertDontSee('Consignment Muff')
            ->set('activeTab', 'unpublished')
            ->assertSee('Consignment Muff')
            ->assertDontSee('Shop Cap');
    }

    public function test_the_dashboard_shows_the_shop_desk(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->paidOrder();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();

        Livewire::actingAs($admin)
            ->test(ShopDeskWidget::class)
            ->assertSee('To pay')
            ->assertSee('To send')
            ->assertSee('Shop sales')
            ->assertSee('1');
    }

    private function paidOrder(?Product $product = null): Order
    {
        $product ??= Product::create([
            'name' => 'Cap', 'slug' => 'paid-cap-'.uniqid(), 'category' => 'Headwear',
            'price_cents' => 32000, 'stock_qty' => 4, 'is_active' => true,
        ]);

        $order = Order::create([
            'customer_name' => 'Jane',
            'email' => 'jane@example.com',
            'phone' => '0820000000',
            'address_line_1' => '1 Range Road',
            'suburb' => 'Hartbeespoort',
            'city' => 'Hartbeespoort',
            'province' => 'North West',
            'postal_code' => '0216',
            'subtotal_cents' => 96000,
            'shipping_cents' => 0,
            'status' => OrderStatus::Paid,
        ]);
        $order->orderItems()->create([
            'product_id' => $product->id,
            'name_snapshot' => 'Test Cap',
            'price_cents_snapshot' => 32000,
            'qty' => 3,
        ]);
        $order->payment()->create([
            'method' => PaymentMethod::Eft,
            'amount_cents' => 96000,
            'status' => PaymentStatus::Paid,
            'reference' => $order->reference,
            'paid_at' => now(),
        ]);

        return $order;
    }
}
