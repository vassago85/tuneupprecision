<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\MarkPaid;
use App\Enums\BookingStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\TrainingEventStatus;
use App\Models\Booking;
use App\Models\CourseTemplate;
use App\Models\Order;
use App\Models\Product;
use App\Models\TrainingEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MarkPaidTest extends TestCase
{
    use RefreshDatabase;

    public function test_marking_an_order_paid_decrements_stock_and_sets_status(): void
    {
        Mail::fake();

        $product = Product::create([
            'name' => 'Test Cap', 'slug' => 'test-cap', 'category' => 'Headwear',
            'price_cents' => 32000, 'stock_qty' => 10, 'is_active' => true,
        ]);

        $order = Order::create([
            'customer_name' => 'Jane', 'email' => 'jane@example.com',
            'subtotal_cents' => 96000, 'status' => OrderStatus::Pending,
        ]);
        $order->orderItems()->create([
            'product_id' => $product->id,
            'name_snapshot' => $product->name,
            'price_cents_snapshot' => 32000,
            'qty' => 3,
        ]);

        $payment = $order->payment()->create([
            'method' => PaymentMethod::Eft, 'amount_cents' => 96000, 'status' => PaymentStatus::Pending,
        ]);

        app(MarkPaid::class)->handle($payment);

        $this->assertSame(PaymentStatus::Paid, $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);
        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
        $this->assertSame(7, $product->fresh()->stock_qty);
    }

    public function test_marking_a_booking_paid_confirms_it_and_clears_the_hold(): void
    {
        Mail::fake();

        $template = CourseTemplate::create([
            'title' => 'Applied', 'slug' => 'applied', 'base_price_cents' => 340000,
            'default_capacity' => 6, 'is_active' => true,
        ]);
        $event = TrainingEvent::create([
            'course_template_id' => $template->id,
            'starts_on' => now()->addWeeks(3), 'venue' => 'Range',
            'capacity' => 6, 'seats_taken' => 3, 'status' => TrainingEventStatus::Published,
        ]);

        $booking = Booking::create([
            'training_event_id' => $event->id, 'customer_name' => 'Sam', 'email' => 'sam@example.com',
            'seats' => 1, 'amount_cents' => 340000, 'status' => BookingStatus::Pending,
            'hold_expires_at' => now()->addMinutes(30),
        ]);
        $payment = $booking->payment()->create([
            'method' => PaymentMethod::Eft, 'amount_cents' => 340000, 'status' => PaymentStatus::Pending,
        ]);

        app(MarkPaid::class)->handle($payment);

        $this->assertSame(BookingStatus::Confirmed, $booking->fresh()->status);
        $this->assertNull($booking->fresh()->hold_expires_at);
        $this->assertSame(PaymentStatus::Paid, $payment->fresh()->status);
    }

    public function test_mark_paid_is_idempotent(): void
    {
        Mail::fake();

        $product = Product::create([
            'name' => 'Patch', 'slug' => 'patch', 'category' => 'Patch',
            'price_cents' => 15000, 'stock_qty' => 5, 'is_active' => true,
        ]);
        $order = Order::create([
            'customer_name' => 'Kim', 'email' => 'kim@example.com',
            'subtotal_cents' => 15000, 'status' => OrderStatus::Pending,
        ]);
        $order->orderItems()->create([
            'product_id' => $product->id, 'name_snapshot' => 'Patch',
            'price_cents_snapshot' => 15000, 'qty' => 1,
        ]);
        $payment = $order->payment()->create([
            'method' => PaymentMethod::Eft, 'amount_cents' => 15000, 'status' => PaymentStatus::Pending,
        ]);

        app(MarkPaid::class)->handle($payment);
        app(MarkPaid::class)->handle($payment); // second call must not double-decrement

        $this->assertSame(4, $product->fresh()->stock_qty);
    }

    public function test_references_are_generated_in_the_right_format(): void
    {
        $template = CourseTemplate::create([
            'title' => 'Zero', 'slug' => 'zero', 'base_price_cents' => 185000,
            'default_capacity' => 6, 'is_active' => true,
        ]);
        $event = TrainingEvent::create([
            'course_template_id' => $template->id, 'starts_on' => now()->addWeek(),
            'venue' => 'Range', 'capacity' => 6, 'status' => TrainingEventStatus::Published,
        ]);

        $booking = Booking::create([
            'training_event_id' => $event->id, 'customer_name' => 'A', 'email' => 'a@example.com',
            'seats' => 1, 'amount_cents' => 185000,
        ]);
        $order = Order::create([
            'customer_name' => 'B', 'email' => 'b@example.com', 'subtotal_cents' => 15000,
        ]);

        $this->assertMatchesRegularExpression('/^TU-B-\d{6}$/', $booking->reference);
        $this->assertMatchesRegularExpression('/^TU-S-\d{6}$/', $order->reference);
    }
}
