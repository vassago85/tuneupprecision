<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\EventKind;
use App\Enums\TrainingEventStatus;
use App\Enums\UserRole;
use App\Filament\Widgets\KpiStatsWidget;
use App\Filament\Widgets\LowStockProductsWidget;
use App\Filament\Widgets\NeedsActionWidget;
use App\Filament\Widgets\RecentActivityWidget;
use App\Models\Booking;
use App\Models\CourseTemplate;
use App\Models\Product;
use App\Models\Testimonial;
use App\Models\TrainingEvent;
use App\Models\TrainingType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['role' => UserRole::Admin]));
    }

    public function test_action_strip_collapses_when_nothing_is_waiting(): void
    {
        Livewire::test(NeedsActionWidget::class)
            ->assertSee('All caught up');
    }

    public function test_action_strip_lists_only_items_with_work_waiting(): void
    {
        $event = $this->courseDate(now()->addWeek()->toDateString(), seatsTaken: 1);
        Booking::create([
            'training_event_id' => $event->id,
            'customer_name' => 'Alex',
            'email' => 'alex@example.com',
            'seats' => 1,
            'amount_cents' => 185000,
            'status' => BookingStatus::Pending,
        ]);
        Testimonial::factory()->count(2)->create();

        Livewire::test(NeedsActionWidget::class)
            ->assertSee('Course booking awaiting EFT')
            ->assertSee('R1 850.00')
            ->assertSee('Testimonials to approve')
            ->assertDontSee('Shop order awaiting EFT')
            ->assertDontSee('Order to send')
            ->assertDontSee('All caught up');
    }

    public function test_seats_kpi_counts_the_same_dates_as_the_training_table(): void
    {
        $this->courseDate(now()->addWeek()->toDateString(), seatsTaken: 4);
        $this->courseDate(now()->addWeeks(2)->toDateString(), TrainingEventStatus::Draft, seatsTaken: 3);
        $this->courseDate(now()->addMonths(2)->toDateString(), seatsTaken: 5);
        TrainingEvent::create([
            'kind' => EventKind::Competition,
            'title' => 'Royal Flush Steel Challenge',
            'starts_on' => now()->addWeeks(2)->toDateString(),
            'venue' => 'Bela-Bela',
            'capacity' => 40,
            'seats_taken' => 10,
            'status' => TrainingEventStatus::Published,
        ]);

        Livewire::test(KpiStatsWidget::class)
            ->assertSee('4 / 6')
            ->assertSee('Across 1 course date');
    }

    public function test_recent_activity_shows_latest_bookings(): void
    {
        $event = $this->courseDate(now()->addWeek()->toDateString(), seatsTaken: 1);
        Booking::create([
            'training_event_id' => $event->id,
            'customer_name' => 'Alex Shooter',
            'email' => 'alex@example.com',
            'seats' => 1,
            'amount_cents' => 185000,
            'status' => BookingStatus::Confirmed,
        ]);

        Livewire::test(RecentActivityWidget::class)
            ->assertSee('Alex Shooter')
            ->assertSee('Zero to First Steel');
    }

    public function test_low_stock_uses_each_products_reorder_level(): void
    {
        $low = $this->product('Ear Muffs', stock: 2, reorderLevel: 3);
        $fine = $this->product('Shooting Glasses', stock: 2, reorderLevel: 0);
        $unpriced = $this->product('Stil Crin Flags', stock: 0, reorderLevel: 0, priceCents: 0);

        $this->assertTrue(LowStockProductsWidget::canView());

        Livewire::test(LowStockProductsWidget::class)
            ->assertCanSeeTableRecords([$low])
            ->assertCanNotSeeTableRecords([$fine, $unpriced]);
    }

    public function test_low_stock_is_hidden_when_nothing_needs_reordering(): void
    {
        $this->product('Ear Muffs', stock: 10, reorderLevel: 3);

        $this->assertFalse(LowStockProductsWidget::canView());
        $this->get('/admin')->assertOk()->assertDontSee('Low stock');
    }

    private function courseDate(
        string $startsOn,
        TrainingEventStatus $status = TrainingEventStatus::Published,
        int $seatsTaken = 0,
    ): TrainingEvent {
        $template = CourseTemplate::query()->firstOrCreate(
            ['slug' => 'zero-to-first-steel'],
            [
                'training_type_id' => TrainingType::factory()->create()->id,
                'title' => 'Zero to First Steel',
                'base_price_cents' => 185000,
                'default_capacity' => 6,
                'is_active' => true,
            ],
        );

        return $template->trainingEvents()->create([
            'kind' => EventKind::Training,
            'starts_on' => $startsOn,
            'venue' => 'Private range · Gauteng',
            'capacity' => 6,
            'seats_taken' => $seatsTaken,
            'status' => $status,
        ]);
    }

    private function product(string $name, int $stock, int $reorderLevel, int $priceCents = 32000): Product
    {
        return Product::create([
            'name' => $name,
            'category' => 'Hearing',
            'price_cents' => $priceCents,
            'stock_qty' => $stock,
            'reorder_level' => $reorderLevel,
            'is_active' => true,
        ]);
    }
}
